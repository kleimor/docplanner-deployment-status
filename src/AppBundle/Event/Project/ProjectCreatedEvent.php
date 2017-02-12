<?php

declare(strict_types=1);

namespace AppBundle\Event\Project;

class ProjectCreatedEvent extends AbstractProjectEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'project.created';
	}
}
