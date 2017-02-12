<?php

declare(strict_types=1);

namespace AppBundle\Event\Project;

class ProjectDeletingEvent extends AbstractProjectEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'project.deleting';
	}
}
