<?php

declare(strict_types=1);

namespace AppBundle\Event\Project;

use AppBundle\Event\AppEvent;
use AppBundle\Model\Project;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractProjectEvent extends Event implements AppEvent
{
	/** @var Project */
	protected $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function getProject(): Project
	{
		return $this->project;
	}
}
