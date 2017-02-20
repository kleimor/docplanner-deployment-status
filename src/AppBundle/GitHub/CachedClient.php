<?php

declare(strict_types=1);

namespace AppBundle\GitHub;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class CachedClient implements ClientInterface
{
	/** @var ClientInterface */
	protected $client;

	/** @var AdapterInterface */
	private $cache;

	public function __construct(ClientInterface $client, AdapterInterface $cache)
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
	public function getCommits(string $owner, string $repo, string $ref, int $daysBack): array
	{
		return $this->getFromCache([__METHOD__, $owner, $repo, $ref, $daysBack],
			function () use ($owner, $repo, $ref, $daysBack)
			{
				return $this->client->getCommits($owner, $repo, $ref, $daysBack);
			}
		);
	}

	/** {@inheritdoc} */
	public function getStatuses(string $owner, string $repo, string $ref): array
	{
		return $this->getFromCache([__METHOD__, $owner, $repo, $ref],
			function () use ($owner, $repo, $ref)
			{
				return $this->client->getStatuses($owner, $repo, $ref);
			}
		);
	}

	/** {@inheritdoc} */
	public function getLatestDeployment(string $owner, string $repo, string $stage, string $ref): array
	{
		return $this->getFromCache([__METHOD__, $owner, $repo, $stage, $ref],
			function () use ($owner, $repo, $stage, $ref)
			{
				return $this->client->getLatestDeployment($owner, $repo, $stage, $ref);
			}
		);
	}

	/** {@inheritdoc} */
	public function getDeploymentStatuses(string $owner, string $repo, int $deploymentId): array
	{
		return $this->getFromCache([__METHOD__, $owner, $repo, $deploymentId],
			function () use ($owner, $repo, $deploymentId)
			{
				return $this->client->getDeploymentStatuses($owner, $repo, $deploymentId);
			}
		);
	}

	protected function getFromCache(array $keyChunks, callable $callback, int $ttl = 86400)
	{
		$key = implode('_', $keyChunks);
		$key = strtr($key, '{}()/\@:', '________');

		$item = $this->cache->getItem($key);
		if (!$item->isHit())
		{
			$data = $callback();
			$item->set($data);
			$item->expiresAfter($ttl);
			$this->cache->save($item);
		}

		return $item->get();
	}
}
