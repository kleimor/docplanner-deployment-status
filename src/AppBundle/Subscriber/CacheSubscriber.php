<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\GitHub\DeploymentEvent;
use AppBundle\Event\GitHub\DeploymentStatusEvent;
use AppBundle\Event\GitHub\PushEvent;
use AppBundle\Event\GitHub\StatusEvent;
use AppBundle\Event\Project\ProjectCreatingEvent;
use AppBundle\Event\Project\ProjectDeletingEvent;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class CacheSubscriber
{
	/** @var TagAwareAdapterInterface */
	private $cache;

	public function __construct(TagAwareAdapterInterface $cache)
	{
		$this->cache = $cache;
	}

	public function onProjectCreating(ProjectCreatingEvent $event)
	{
		$project = $event->getProject();

		$this->clearCache(["owner/{$project->getOwner()}"]);
	}

	public function onProjectDeleting(ProjectDeletingEvent $event)
	{
		$project = $event->getProject();

		$this->clearCache(["owner/{$project->getOwner()}"]);
	}

	public function onGithubDeployment(DeploymentEvent $event)
	{
		$payload = $event->getPayload();

		$owner = $payload['repository']['owner']['login'];
		$repo  = $payload['repository']['name'];
		$stage = $payload['deployment']['environment'];

		$this->clearCache(["owner/{$owner}/repo/{$repo}/stage/{$stage}"]);
	}

	public function onGithubDeploymentStatus(DeploymentStatusEvent $event)
	{
		$payload = $event->getPayload();

		$owner = $payload['repository']['owner']['login'];
		$repo  = $payload['repository']['name'];
		$stage = $payload['deployment']['environment'];

		$this->clearCache(["owner/{$owner}/repo/{$repo}/stage/{$stage}"]);
	}

	public function onGithubPush(PushEvent $event)
	{
		$payload = $event->getPayload();

		$owner = $payload['repository']['owner']['login'];
		$repo  = $payload['repository']['name'];

		$this->clearCache(["owner/{$owner}/repo/{$repo}"]);
	}

	public function onGithubStatus(StatusEvent $event)
	{
		$payload = $event->getPayload();

		$owner = $payload['repository']['owner']['login'];
		$repo  = $payload['repository']['name'];

		$this->clearCache(["owner/{$owner}/repo/{$repo}"]);
	}

	protected function clearCache(array $tags)
	{
		$this->cache->invalidateTags(
			array_map(
				function (string $tag)
				{
					return $this->sanitize($tag);
				},
				$tags
			)
		);
	}

	protected function sanitize(string $value): string
	{
		return strtr($value, '{}()/\@:', '________');
	}
}
