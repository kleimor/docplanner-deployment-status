<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class GitHubEventFactory
{
	/**
	 * @param string $githubEventType
	 * @param array  $payload
	 *
	 * @return GitHubEventInterface
	 * @throws \InvalidArgumentException
	 */
	public static function createEvent(string $githubEventType, array $payload): GitHubEventInterface
	{
		switch ($githubEventType)
		{
			case DeleteEvent::getGitHubEventType():
				return new DeleteEvent($payload);
				break;

			case DeploymentEvent::getGitHubEventType():
				return new DeploymentEvent($payload);
				break;

			case DeploymentStatusEvent::getGitHubEventType():
				return new DeploymentStatusEvent($payload);
				break;

			case PushEvent::getGitHubEventType():
				return new PushEvent($payload);
				break;

			case PingEvent::getGitHubEventType():
				return new PingEvent($payload);
				break;

			case StatusEvent::getGitHubEventType():
				return new StatusEvent($payload);
				break;
		}

		throw new \InvalidArgumentException('Unsupported GitHub event type');
	}
}
