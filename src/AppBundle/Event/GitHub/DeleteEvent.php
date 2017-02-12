<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class DeleteEvent extends AbstractGitHubEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'github.delete';
	}

	/** {@inheritdoc} */
	public static function getGitHubEventType(): string
	{
		return 'delete';
	}
}
