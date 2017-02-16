<?php

declare(strict_types=1);

namespace AppBundle\GitHub;

use AppBundle\Model\GithubWebhookQuery;
use AppBundle\Model\Project;

class HookManager
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
			}
		}
	}
}
