<?php

declare(strict_types=1);

namespace AppBundle\GitHub;

use AppBundle\Event\Project\ProjectGithubWebhookCreatedEvent;
use AppBundle\Event\Project\ProjectGithubWebhookDeletedEvent;
use AppBundle\Model\GithubWebhookQuery;
use AppBundle\Model\Project;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HookManager
{
	/** @var ClientInterface */
	protected $github;

	/** @var string[] */
	private $subscribedEvents;

	/** @var EventDispatcherInterface */
	private $eventDispatcher;

	public function __construct(
		ClientInterface $github,
		array $subscribedEvents,
		EventDispatcherInterface $eventDispatcher
	) {
		$this->github           = $github;
		$this->subscribedEvents = $subscribedEvents;
		$this->eventDispatcher  = $eventDispatcher;
	}

	public function installHooks(Project $project)
	{
		$webhookId = $this->github->createHook(
			$project->getOwner(),
			$project->getRepo(),
			$this->subscribedEvents
		);

		$webhook = (new GithubWebhookQuery)
			->filterByProjectId($project->getId())
			->filterByGithubId($webhookId)
			->findOneOrCreate();

		$webhook
			->setEvents(array_merge($webhook->getEvents(), $this->subscribedEvents))
			->save();

		$event = new ProjectGithubWebhookCreatedEvent($project, $webhook);
		$this->eventDispatcher->dispatch(ProjectGithubWebhookCreatedEvent::getEventName(), $event);
	}

	public function removeHooks(Project $project)
	{
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

				$event = new ProjectGithubWebhookDeletedEvent($project, $githubWebhook);
				$this->eventDispatcher->dispatch(ProjectGithubWebhookDeletedEvent::getEventName(), $event);
			}
		}
	}
}
