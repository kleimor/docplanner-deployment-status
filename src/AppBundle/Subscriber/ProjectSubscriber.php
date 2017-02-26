<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\Project\AbstractProjectEvent;
use AppBundle\Event\Project\AbstractProjectGithubWebhookEvent;
use AppBundle\Event\Project\ProjectCreatedEvent;
use AppBundle\Event\Project\ProjectDeletingEvent;
use AppBundle\Event\Stage\AbstractStageEvent;
use AppBundle\GitHub\HookManager;
use AppBundle\Model\GithubWebhook;
use AppBundle\Model\Project;
use AppBundle\Model\Stage;

class ProjectSubscriber
{
	/** @var HookManager */
	private $hookManager;

	/** @var \Pusher */
	private $pusher;

	public function __construct(HookManager $hookManager, \Pusher $pusher)
	{
		$this->hookManager = $hookManager;
		$this->pusher      = $pusher;
	}

	public function onProjectCreated(ProjectCreatedEvent $event)
	{
		$project = $event->getProject();
		$this->hookManager->installHooks($project);
	}

	public function onProjectDeleting(ProjectDeletingEvent $event)
	{
		$project = $event->getProject();
		$this->hookManager->removeHooks($project);
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

	public function onGithubWebhookEvent(AbstractProjectGithubWebhookEvent $event)
	{
		$githubWebhook = $event->getGithubWebhook();
		$project       = $event->getProject();

		$this->pusher->trigger(['public'], $event::getEventName(), [
			'project'        => $this->getProjectPayload($project),
			'github_webhook' => $this->getGithubWebhookPayload($githubWebhook),
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

	protected function getGithubWebhookPayload(GithubWebhook $githubWebhook): array
	{
		return [
			'github_id' => $githubWebhook->getGithubId(),
			'events'    => $githubWebhook->getEvents(),
		];
	}
}
