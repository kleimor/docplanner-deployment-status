<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\Project\ProjectCreatingEvent;
use AppBundle\GitHub\Client;
use AppBundle\Model\GithubWebhook;

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

	public function onProjectCreating(ProjectCreatingEvent $event)
	{
		$project = $event->getProject();

		$webhookId = $this->github->createHook(
			$project->getOwner(),
			$project->getRepo(),
			$this->subscribedEvents
		);

		(new GithubWebhook)
			->setProjectId($project->getId())
			->setGithubId($webhookId)
			->save();
	}
}
