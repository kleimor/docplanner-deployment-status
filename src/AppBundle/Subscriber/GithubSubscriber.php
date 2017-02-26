<?php

declare(strict_types=1);

namespace AppBundle\Subscriber;

use AppBundle\Event\GitHub\GitHubEventInterface;

class GithubSubscriber
{
	/** @var \Pusher */
	private $pusher;

	public function __construct(\Pusher $pusher)
	{
		$this->pusher = $pusher;
	}

	public function onGithubEvent(GitHubEventInterface $event)
	{
		$this->pusher->trigger(['public'], $event::getEventName(), [
			'event'   => $event::getGitHubEventType(),
			'payload' => $event->getPayload(),
		]);
	}


}
