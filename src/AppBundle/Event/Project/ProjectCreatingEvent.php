<?php

declare(strict_types=1);

namespace AppBundle\Event\Project;

class ProjectCreatingEvent extends ProjectEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'project.creating';
	}
}
