<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class DeploymentStatusEvent extends AbstractGitHubEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'github.deployment_status';
	}

	/** {@inheritdoc} */
	public static function getGitHubEventType(): string
	{
		return 'deployment_status';
	}
}
