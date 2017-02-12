<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class PushEvent extends AbstractGitHubEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'github.push';
	}

	/** {@inheritdoc} */
	public static function getGitHubEventType(): string
	{
		return 'push';
	}
}
