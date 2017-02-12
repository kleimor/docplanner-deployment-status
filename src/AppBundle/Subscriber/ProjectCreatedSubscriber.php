<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\Project\ProjectCreatedEvent;
use AppBundle\GitHub\Client;

class ProjectCreatedSubscriber
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

	public function onProjectCreated(ProjectCreatedEvent $event)
	{
		$project = $event->getProject();

		foreach ($this->subscribedEvents as $subscribedEvent)
		{
			$this->github->subscribeToEvent(
				$project->getOwner(),
				$project->getRepo(),
				$subscribedEvent
			);
		}

	}
}
