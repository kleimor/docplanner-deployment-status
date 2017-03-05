<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class GitHubEventFactory
{
	/**
	 * @param string $githubEventType
	 * @param array  $githubPayload
	 *
	 * @return GitHubEventInterface|DeploymentEvent|DeploymentStatusEvent|PushEvent|PingEvent|StatusEvent
	 * @throws \InvalidArgumentException
	 */
	public static function createEvent(string $githubEventType, array $githubPayload): GitHubEventInterface
	{
		switch ($githubEventType)
		{
			case DeploymentEvent::getGitHubEventType():
				return new DeploymentEvent($githubPayload);
				break;

			case DeploymentStatusEvent::getGitHubEventType():
				return new DeploymentStatusEvent($githubPayload);
				break;

			case PushEvent::getGitHubEventType():
				return new PushEvent($githubPayload);
				break;

			case PingEvent::getGitHubEventType():
				return new PingEvent($githubPayload);
				break;

			case StatusEvent::getGitHubEventType():
				return new StatusEvent($githubPayload);
				break;
		}

		throw new \InvalidArgumentException('Unsupported GitHub event type');
	}
}
