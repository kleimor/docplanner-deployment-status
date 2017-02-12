<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class PingEvent extends AbstractGitHubEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'github.ping';
	}

	/** {@inheritdoc} */
	public static function getGitHubEventType(): string
	{
		return 'ping';
	}
}
