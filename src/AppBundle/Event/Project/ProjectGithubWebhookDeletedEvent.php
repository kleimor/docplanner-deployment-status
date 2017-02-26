<?php

declare(strict_types=1);

namespace AppBundle\Event\Project;

class ProjectGithubWebhookDeletedEvent extends AbstractProjectGithubWebhookEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'project.github_webhook.deleted';
	}
}
