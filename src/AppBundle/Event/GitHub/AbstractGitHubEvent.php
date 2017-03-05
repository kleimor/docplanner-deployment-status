<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractGitHubEvent extends Event implements GitHubEventInterface
{
	/** @var array */
	protected $githubPayload;

	public function __construct(array $githubPayload)
	{
		$this->githubPayload = $githubPayload;
	}

	/** {@inheritdoc} */
	public function getPayload(): array
	{
		$payload = [
			'project' => [
				'owner' => $this->getOwner(),
				'repo'  => $this->getRepo(),
			],
		];

		if ($this instanceof StageAwareInterface)
		{
			$payload['stage'] = [
				'name' => $this->getStage(),
			];
		}

		if ($this instanceof BranchAwareInterface)
		{
			$payload['branch'] = [
				'name' => $this->getBranch(),
			];
		}

		return $payload;
	}

	/** {@inheritdoc} */
	public function getOwner(): string
	{
		return $this->githubPayload['repository']['owner']['login'];
	}

	/** {@inheritdoc} */
	public function getRepo(): string
	{
		return $this->githubPayload['repository']['name'];
	}
}
