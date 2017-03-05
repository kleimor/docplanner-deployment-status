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
			'commits',
			[
				'owner' => $owner,
				'repo'  => $repo,
				'ref'   => $ref,
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
			'commits_diff',
			[
				'owner'   => $owner,
				'repo'    => $repo,
				'baseRef' => $baseRef,
				'headRef' => $headRef,
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
			'statuses',
			[
				'owner' => $owner,
				'repo'  => $repo,
				'ref'   => $ref,
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
			'latest_deployment',
			[
				'owner' => $owner,
				'repo'  => $repo,
				'stage' => $stage,
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
			'deployment_statuses',
			[
				'owner'        => $owner,
				'repo'         => $repo,
				'deploymentId' => $deploymentId,
			],
			function () use ($owner, $repo, $deploymentId)
			{
				return $this->client->getDeploymentStatuses($owner, $repo, $deploymentId);
			}
		);
	}

	protected function getFromCache(string $keyName, array $keyChunks, callable $callback, int $ttl = 86400)
	{
		$tags   = iterator_to_array($this->createTags($keyName, $keyChunks));
		$key    = array_pop($tags);
		$tags[] = $key;

		$item = $this->cache->getItem($key);
		if (!$item->isHit())
		{
			$data = $callback();
			$item->set($data);
			$item->expiresAfter($ttl);
			$item->tag($tags);
			$this->cache->save($item);
		}

		return $item->get();
	}

	protected function createTags(string $name, array $chunks): \Generator
	{
		yield $name;

		$prefix = '';
		foreach ($chunks as $key => $value)
		{
			$prefix .= "{$key}/{$value}/";

			yield $this->sanitize(rtrim($prefix, '/'));
			yield $this->sanitize("{$prefix}{$name}");

		}
	}

	protected function sanitize(string $value): string
	{
		return strtr($value, '{}()/\@:', '________');
	}
}
