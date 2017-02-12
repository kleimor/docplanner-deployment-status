<?php

declare(strict_types=1);

namespace AppBundle\Event\Stage;

class StageCreatedEvent extends AbstractStageEvent
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'stage.created';
	}
}
