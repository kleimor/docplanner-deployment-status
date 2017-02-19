<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\GitHub\GitHubEventInterface;
use AppBundle\Event\Project\AbstractProjectEvent;
use AppBundle\Event\Stage\AbstractStageEvent;
use AppBundle\Model\Project;
use AppBundle\Model\Stage;

class GithubSubscriber
{
	/** @var \Pusher */
	private $pusher;

	public function __construct(\Pusher $pusher)
	{
		$this->pusher = $pusher;
	}

	public function onGithubEvent(GitHubEventInterface $event)
	{
		$this->pusher->trigger(['public'], $event::getEventName(), [
			'event'   => $event::getGitHubEventType(),
			'payload' => $event->getPayload(),
		]);
	}

	public function onProjectEvent(AbstractProjectEvent $event)
	{
		$project = $event->getProject();

		$this->pusher->trigger(['public'], $event::getEventName(), [
			'project' => $this->getProjectPayload($project),
		]);
	}

	public function onStageEvent(AbstractStageEvent $event)
	{
		$stage   = $event->getStage();
		$project = $stage->getProject();

		$this->pusher->trigger(['public'], $event::getEventName(), [
			'project' => $this->getProjectPayload($project),
			'stage'   => $this->getStagePayload($stage),
		]);
	}

	protected function getProjectPayload(Project $project): array
	{
		return [
			'owner'  => $project->getOwner(),
			'repo'   => $project->getRepo(),
			'stages' => array_map([$this, 'getStagePayload'], $project->getStages()->getData()),
		];
	}

	protected function getStagePayload(Stage $stage): array
	{
		return [
			'name'           => $stage->getName(),
			'tracked_branch' => $stage->getTrackedBranch(),
		];
	}
}
