<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

use AppBundle\Event\AppEvent;

interface GitHubEventInterface extends AppEvent
{
	public static function getGitHubEventType(): string;

	public function getPayload(): array;

	public function getOwner(): string;

	public function getRepo(): string;
}
