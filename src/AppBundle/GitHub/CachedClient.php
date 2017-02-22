<?php

declare(strict_types=1);

namespace AppBundle\GitHub;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class CachedClient implements ClientInterface
{
	/** @var ClientInterface */
	protected $client;

	/** @var TagAwareAdapterInterface */
	private $cache;

	public function __construct(ClientInterface $client, TagAwareAdapterInterface $cache)
	{
		$this->client = $client;
		$this->cache  = $cache;
	}

	/** {@inheritdoc} */
	public function createHook(string $owner, string $repo, array $events): int
	{
		return $this->client->createHook($owner, $repo, $events);
	}

	/** {@inheritdoc} */
	public function deleteHook(string $owner, string $repo, int $webhookId): bool
	{
		return $this->client->deleteHook($owner, $repo, $webhookId);
	}

	/** {@inheritdoc} */
	public function getCommits(string $owner, string $repo, string $ref): array
	{
		return $this->getFromCache(
			[__METHOD__, $owner, $repo, $ref],
			[
				"owner/{$owner}",
				"owner/{$owner}/commits",
				"owner/{$owner}/repo/{$repo}",
				"owner/{$owner}/repo/{$repo}/commits",
			],
			function () use ($owner, $repo, $ref)
			{
				return $this->client->getCommits($owner, $repo, $ref);
			}
		);
	}

	/** {@inheritdoc} */
	public function getCommitsDiff(string $owner, string $repo, string $baseRef, string $headRef): array
	{
		return $this->getFromCache(
			[__METHOD__, $owner, $repo, $baseRef, $headRef],
			[
				"owner/{$owner}",
				"owner/{$owner}/commits_diff",
				"owner/{$owner}/repo/{$repo}",
				"owner/{$owner}/repo/{$repo}/commits_diff",
			],
			function () use ($owner, $repo, $baseRef, $headRef)
			{
				return $this->client->getCommitsDiff($owner, $repo, $baseRef, $headRef);
			}
		);
	}

	/** {@inheritdoc} */
	public function getStatuses(string $owner, string $repo, string $ref): array
	{
		return $this->getFromCache(
			[__METHOD__, $owner, $repo, $ref],
			[
				"owner/{$owner}",
				"owner/{$owner}/statuses",
				"owner/{$owner}/repo/{$repo}",
				"owner/{$owner}/repo/{$repo}/statuses",
			],
			function () use ($owner, $repo, $ref)
			{
				return $this->client->getStatuses($owner, $repo, $ref);
			}
		);
	}

	/** {@inheritdoc} */
	public function getLatestDeployment(string $owner, string $repo, string $stage): array
	{
		return $this->getFromCache(
			[__METHOD__, $owner, $repo, $stage],
			[
				"owner/{$owner}",
				"owner/{$owner}/latest_deployment",
				"owner/{$owner}/repo/{$repo}",
				"owner/{$owner}/repo/{$repo}/latest_deployment",
				"owner/{$owner}/repo/{$repo}/stage/{$stage}",
				"owner/{$owner}/repo/{$repo}/stage/{$stage}/latest_deployment",
			],
			function () use ($owner, $repo, $stage)
			{
				return $this->client->getLatestDeployment($owner, $repo, $stage);
			}
		);
	}

	/** {@inheritdoc} */
	public function getDeploymentStatuses(string $owner, string $repo, int $deploymentId): array
	{
		return $this->getFromCache(
			[__METHOD__, $owner, $repo, $deploymentId],
			[
				"owner/{$owner}",
				"owner/{$owner}/deployment_statuses",
				"owner/{$owner}/repo/{$repo}",
				"owner/{$owner}/repo/{$repo}/deployment_statuses",
			],
			function () use ($owner, $repo, $deploymentId)
			{
				return $this->client->getDeploymentStatuses($owner, $repo, $deploymentId);
			}
		);
	}

	protected function getFromCache(array $keyChunks, array $tags, callable $callback, int $ttl = 86400)
	{
		$key = $this->sanitize(implode('_', $keyChunks));

		$item = $this->cache->getItem($key);
		if (!$item->isHit())
		{
			$data = $callback();
			$item->set($data);
			$item->expiresAfter($ttl);
			$item->tag(
				array_map(
					function (string $tag)
					{
						return $this->sanitize($tag);
					},
					$tags
				)
			);
			$this->cache->save($item);
		}

		return $item->get();
	}

	protected function sanitize(string $value): string
	{
		return strtr($value, '{}()/\@:', '________');
	}
}
