<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\Project\ProjectDeletingEvent;
use AppBundle\GitHub\Client;

class ProjectDeletingSubscriber
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

	public function onProjectDeleting(ProjectDeletingEvent $event)
	{
		$project        = $event->getProject();
		$githubWebhooks = $project->getGithubWebhooks();

		foreach ($githubWebhooks as $githubWebhook)
		{
			$deleted = $this->github->deleteHook(
				$project->getOwner(),
				$project->getRepo(),
				$githubWebhook->getGithubId()
			);

			if ($deleted)
			{
				$githubWebhook->delete();
			}
		}
	}
}
