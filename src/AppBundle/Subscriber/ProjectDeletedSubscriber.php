<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\Project\ProjectDeletedEvent;
use AppBundle\GitHub\Client;

class ProjectDeletedSubscriber
{
	/** @var Client */
	protected $github;

	/** @var string[] */
	private $subscribedEvents;

	public function __construct(Client $github, array $subscribedEvents)
	{
		$this->github           = $github;
		$this->subscribedEvents = $subscribedEvents;
	}

	public function onProjectDeleted(ProjectDeletedEvent $event)
	{
		$project = $event->getProject();

		foreach ($this->subscribedEvents as $subscribedEvent)
		{
			$this->github->unsubscribeFromEvent(
				$project->getOwner(),
				$project->getRepo(),
				$subscribedEvent
			);
		}

	}
}
