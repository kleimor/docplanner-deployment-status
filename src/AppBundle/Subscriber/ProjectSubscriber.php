<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\Project\ProjectCreatedEvent;
use AppBundle\Event\Project\ProjectDeletingEvent;
use AppBundle\GitHub\HookManager;

class ProjectSubscriber
{
	/** @var HookManager */
	private $hookManager;

	public function __construct(HookManager $hookManager)
	{
		$this->hookManager = $hookManager;
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
}
