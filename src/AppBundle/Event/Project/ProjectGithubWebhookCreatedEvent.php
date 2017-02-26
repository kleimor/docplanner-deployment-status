<?php

declare(strict_types=1);

namespace AppBundle\Event\Project;

class ProjectGithubWebhookCreatedEvent extends AbstractProjectGithubWebhookEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'project.github_webhook.created';
	}
}
