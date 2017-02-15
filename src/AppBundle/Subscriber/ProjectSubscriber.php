<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\Project\ProjectCreatedEvent;
use AppBundle\Event\Project\ProjectDeletedEvent;
use AppBundle\Event\Project\ProjectDeletingEvent;
use AppBundle\GitHub\HookManager;

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

		$this->pusher->trigger(['public'], $event::getEventName(), [
			'owner' => $project->getOwner(),
			'repo'  => $project->getRepo(),
		]);
	}

	public function onProjectDeleting(ProjectDeletingEvent $event)
	{
		$project = $event->getProject();
		$this->hookManager->removeHooks($project);
	}

	public function onProjectDeleted(ProjectDeletedEvent $event)
	{
		$project = $event->getProject();
		$this->pusher->trigger(['public'], $event::getEventName(), [
			'owner' => $project->getOwner(),
			'repo'  => $project->getRepo(),
		]);
	}
}
