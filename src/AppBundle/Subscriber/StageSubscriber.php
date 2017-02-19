<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\Stage\StageCreatedEvent;
use AppBundle\Event\Stage\StageDeletedEvent;

class StageSubscriber
{
	/** @var \Pusher */
	private $pusher;

	public function __construct(\Pusher $pusher)
	{
		$this->pusher = $pusher;
	}

	public function onStageCreated(StageCreatedEvent $event)
	{
		$stage   = $event->getStage();
		$project = $stage->getProject();

		$this->pusher->trigger(['public'], $event::getEventName(), [
			'owner' => $project->getOwner(),
			'repo'  => $project->getRepo(),
			'stage' => $stage->getName(),
		]);
	}

	public function onStageDeleted(StageDeletedEvent $event)
	{
		$stage   = $event->getStage();
		$project = $stage->getProject();

		$this->pusher->trigger(['public'], $event::getEventName(), [
			'owner' => $project->getOwner(),
			'repo'  => $project->getRepo(),
			'stage' => $stage->getName(),
		]);
	}
}
