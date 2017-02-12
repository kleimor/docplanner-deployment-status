<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractGitHubEvent extends Event implements GitHubEventInterface
{
	/** @var array */
	protected $payload;

	public function __construct(array $payload)
	{
		$this->payload = $payload;
	}

	/** {@inheritdoc} */
	public function getPayload(): array
	{
		return $this->payload;
	}
}
