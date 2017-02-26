<?php

declare(strict_types=1);

namespace AppBundle\Event\Project;

use AppBundle\Event\AppEvent;
use AppBundle\Model\GithubWebhook;
use AppBundle\Model\Project;

abstract class AbstractProjectGithubWebhookEvent extends AbstractProjectEvent implements AppEvent
{
	/** @var GithubWebhook */
	private $githubWebhook;

	public function __construct(Project $project, GithubWebhook $githubWebhook)
	{
		parent::__construct($project);
		$this->githubWebhook = $githubWebhook;
	}

	public function getGithubWebhook(): GithubWebhook
	{
		return $this->githubWebhook;
	}
}
