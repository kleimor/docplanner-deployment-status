<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class StatusEvent extends AbstractGitHubEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'github.status';
	}

	/** {@inheritdoc} */
	public static function getGitHubEventType(): string
	{
		return 'status';
	}
}
