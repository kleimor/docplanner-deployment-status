<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class DeploymentEvent extends AbstractGitHubEvent
{
	/** {@inheritdoc} */
	public function getPayload(): array
	{
		return array_merge_recursive($this->payload, [
			'deployment' => [
				'environment' => $this->payload['deployment']['environment'],
			],
		]);
	}

	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'github.deployment';
	}

	/** {@inheritdoc} */
	public static function getGitHubEventType(): string
	{
		return 'deployment';
	}
}
