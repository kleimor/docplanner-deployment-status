<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class DeploymentEvent extends AbstractGitHubEvent implements StageAwareInterface
{
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

	/** {@inheritdoc} */
	public function getStage(): string
	{
		return $this->githubPayload['deployment']['environment'];
	}
}
