<?php

declare(strict_types=1);

namespace AppBundle\Event;

interface AppEvent
{
	public static function getEventName(): string;
}
