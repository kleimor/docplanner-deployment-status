<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

interface StageAwareInterface
{
	public function getStage(): string;
}
