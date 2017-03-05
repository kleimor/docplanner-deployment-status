<?php

declare(strict_types=1);

namespace AppBundle\Event\GitHub;

class PushEvent extends AbstractGitHubEvent implements BranchAwareInterface
{
	/** {@inheritdoc} */
	public static function getEventName(): string
	{
		return 'github.push';
	}

	/** {@inheritdoc} */
	public static function getGitHubEventType(): string
	{
		return 'push';
	}

	/** {@inheritdoc} */
	public function getBranch(): string
	{
		$ref = $this->githubPayload['ref'];

		foreach (['refs/heads/'] as $prefix)
		{
			if (0 === strpos($ref, $prefix))
			{
				$ref = substr($ref, strlen($prefix));
			}
		}

		return $ref;
	}
}
