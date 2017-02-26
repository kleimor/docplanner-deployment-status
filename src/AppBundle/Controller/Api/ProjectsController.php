<?php

declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Event\Project\ProjectCreatedEvent;
use AppBundle\Event\Project\ProjectCreatingEvent;
use AppBundle\Event\Project\ProjectDeletedEvent;
use AppBundle\Event\Project\ProjectDeletingEvent;
use AppBundle\Model\GithubWebhookQuery;
use AppBundle\Model\Project;
use AppBundle\Model\ProjectQuery;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Propel\Runtime\ActiveQuery\Criteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectsController extends Controller
{
	/**
	 * @ApiDoc(
	 *     description="Track a project",
	 *     views={"default","v1"}
	 * )
	 */
	public function createAction(string $owner, string $repo): Response
	{
		$con = $this->get('propel.connection.default');
		$con->beginTransaction();
		try
		{
			$project = (new Project)
				->setOwner($owner)
				->setRepo($repo);
			$project->save();

			$event = new ProjectCreatingEvent($project);
			$this->get('event_dispatcher')->dispatch(ProjectCreatingEvent::getEventName(), $event);

			$con->commit();
		}
		catch (\Exception $exception)
		{
			$con->rollBack();
			throw $exception;
		}

		$event = new ProjectCreatedEvent($project);
		$this->get('event_dispatcher')->dispatch(ProjectCreatedEvent::getEventName(), $event);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @ApiDoc(
	 *     description="List tracked projects and their stages",
	 *     views={"default","v1"}
	 * )
	 */
	public function listAction(): JsonResponse
	{
		// @formatter:off
		$projects = (new ProjectQuery)
			->orderByOwner()
			->orderByRepo()
			->useStageQuery(null, Criteria::LEFT_JOIN)
				->orderByName()
			->endUse()
			->with('Stage')
			->find();
		// @formatter:on

		$data = [];
		foreach ($projects as $project)
		{
			$projectData = [
				'owner'       => $project->getOwner(),
				'repo'        => $project->getRepo(),
				'base_branch' => $project->getBaseBranch(),
				'stages'      => [],
			];

			foreach ($project->getStages() as $stage)
			{
				$projectData['stages'][] = [
					'title'          => $stage->getTitle(),
					'name'           => $stage->getName(),
					'tracked_branch' => $stage->getTrackedBranch(),
				];
			}

			$data[] = $projectData;
		}

		return new JsonResponse($data);
	}

	/**
	 * @ApiDoc(
	 *     description="Untrack a project",
	 *     views={"default", "v1"}
	 * )
	 *
	 * @ParamConverter("project", options={"mapping"={"owner":"owner", "repo":"repo"}})
	 */
	public function deleteAction(Project $project): Response
	{
		$event = new ProjectDeletingEvent($project);
		$this->get('event_dispatcher')->dispatch(ProjectDeletingEvent::getEventName(), $event);

		$project->delete();

		$event = new ProjectDeletedEvent($project);
		$this->get('event_dispatcher')->dispatch(ProjectDeletedEvent::getEventName(), $event);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @ApiDoc(
	 *     description="Clear cache for a project",
	 *     views={"default", "v1"}
	 * )
	 *
	 * @ParamConverter("project", options={"mapping"={"owner":"owner", "repo":"repo"}})
	 */
	public function clearCacheAction(Project $project): Response
	{
		$this->get('app.cache')->invalidateTags(["owner_{$project->getOwner()}_repo_{$project->getRepo()}"]);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @ApiDoc(
	 *     description="Get Github hooks",
	 *     views={"default","v1"}
	 * )
	 *
	 * @ParamConverter("project", options={"mapping"={"owner":"owner", "repo":"repo"}})
	 */
	public function listGithubHooksAction(Project $project): Response
	{
		$githubWebhooks = (new GithubWebhookQuery)
			->filterByProject($project)
			->innerJoinWith('Project')
			->find();

		$data = [];
		foreach ($githubWebhooks as $githubWebhook)
		{
			$project = $githubWebhook->getProject();

			$data[] = [
				'github_webhook' => [
					'github_id' => $githubWebhook->getGithubId(),
					'events'    => $githubWebhook->getEvents(),
				],
				'project'        => [
					'owner' => $project->getOwner(),
					'repo'  => $project->getRepo(),
				],
			];
		}

		return new JsonResponse($data);
	}

	/**
	 * @ApiDoc(
	 *     description="Install Github hooks",
	 *     views={"default","v1"}
	 * )
	 *
	 * @ParamConverter("project", options={"mapping"={"owner":"owner", "repo":"repo"}})
	 */
	public function installGithubHooksAction(Project $project): Response
	{
		$this->get('github.hook_manager')->installHooks($project);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @ApiDoc(
	 *     description="Remove Github hooks",
	 *     views={"default","v1"}
	 * )
	 *
	 * @ParamConverter("project", options={"mapping"={"owner":"owner", "repo":"repo"}})
	 */
	public function removeGithubHooksAction(Project $project): Response
	{
		$this->get('github.hook_manager')->removeHooks($project);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}
}
