<?php

declare(strict_types=1);

namespace AppBundle\Event\Stage;

use AppBundle\Event\AppEvent;
use AppBundle\Model\Stage;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractStageEvent extends Event implements AppEvent
{
	/** @var Stage */
	protected $stage;

	public function __construct(Stage $stage)
	{
		$this->stage = $stage;
	}

	public function getStage(): Stage
	{
		return $this->stage;
	}
}
