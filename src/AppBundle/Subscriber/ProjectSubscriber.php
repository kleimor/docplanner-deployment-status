<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\Project\ProjectCreatedEvent;
use AppBundle\Event\Project\ProjectCreatingEvent;
use AppBundle\Event\Project\ProjectDeletedEvent;
use AppBundle\Event\Project\ProjectDeletingEvent;
use AppBundle\GitHub\Client;
use AppBundle\Model\GithubWebhook;

class ProjectSubscriber
{
	/** @var Client */
	protected $github;

	/** @var string[] */
	private $subscribedEvents;

	/** @var \Pusher */
	private $pusher;

	public function __construct(Client $github, array $subscribedEvents, \Pusher $pusher)
	{
		$this->github           = $github;
		$this->subscribedEvents = $subscribedEvents;
		$this->pusher           = $pusher;
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
			->setEvents($this->subscribedEvents)
			->save();
	}

	public function onProjectCreated(ProjectCreatedEvent $event)
	{
		$project = $event->getProject();
		$this->pusher->trigger(['public'], $event::getEventName(), [
			'owner' => $project->getOwner(),
			'repo'  => $project->getRepo(),
		]);
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

	public function onProjectDeleted(ProjectDeletedEvent $event)
	{
		$project = $event->getProject();
		$this->pusher->trigger(['public'], $event::getEventName(), [
			'owner' => $project->getOwner(),
			'repo'  => $project->getRepo(),
		]);
	}
}
