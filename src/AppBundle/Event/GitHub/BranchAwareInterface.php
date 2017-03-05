<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

interface BranchAwareInterface
{
	public function getBranch(): string;
}
