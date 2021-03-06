<?php

declare(strict_types=1);

namespace AppBundle\Event\Project;

class ProjectCreatingEvent extends AbstractProjectEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'project.creating';
	}
}
