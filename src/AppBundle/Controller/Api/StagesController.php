<?php

declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Event\Stage\StageCreatedEvent;
use AppBundle\Event\Stage\StageCreatingEvent;
use AppBundle\Event\Stage\StageDeletedEvent;
use AppBundle\Model\Project;
use AppBundle\Model\Stage;
use AppBundle\Model\StageQuery;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class StagesController extends Controller
{
	/**
	 * @ApiDoc(
	 *     description="Track a stage within a project",
	 *     views={"default","v1"}
	 * )
	 *
	 * @ParamConverter("project", options={"mapping"={"owner":"owner", "repo":"repo"}})
	 */
	public function createAction(Project $project, string $stage): Response
	{
		$stageModel = (new StageQuery)
			->filterByProject($project)
			->filterByName($stage)
			->findOne();

		if (null !== $stageModel)
		{
			throw new ConflictHttpException("Stage {$stage} is already tracked");
		}

		$con = $this->get('propel.connection.default');
		$con->beginTransaction();
		try
		{
			$stageModel = (new Stage)
				->setProjectId($project->getId())
				->setName($stage)
				->setTrackedBranch('master');
			$stageModel->save();

			$event = new StageCreatingEvent($stageModel);
			$this->get('event_dispatcher')->dispatch(StageCreatingEvent::getEventName(), $event);

			$con->commit();
		}
		catch (\Exception $exception)
		{
			$con->rollBack();
			throw $exception;
		}

		$event = new StageCreatedEvent($stageModel);
		$this->get('event_dispatcher')->dispatch(StageCreatedEvent::getEventName(), $event);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @ApiDoc(
	 *     description="Untrack stage within a project",
	 *     views={"default", "v1"}
	 * )
	 *
	 * @ParamConverter("project", options={"mapping"={"owner":"owner", "repo":"repo"}})
	 */
	public function deleteAction(Project $project, string $stage): Response
	{
		$stageModel = (new StageQuery)
			->filterByProject($project)
			->filterByName($stage)
			->findOne();

		if (null === $stageModel)
		{
			throw $this->createNotFoundException("Stage {$stage} not found");
		}

		$stageModel->delete();

		$event = new StageDeletedEvent($stageModel);
		$this->get('event_dispatcher')->dispatch(StageDeletedEvent::getEventName(), $event);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}

	/**
	 * @ApiDoc(
	 *     description="Get stage commits",
	 *     views={"default", "v1"},
	 *     parameters={
	 *         {
	 *             "name": "days_back",
	 *             "dataType": "integer",
	 *             "required": false
	 *         }
	 *     }
	 * )
	 *
	 * @ParamConverter("project", options={"mapping"={"owner":"owner", "repo":"repo"}})
	 */
	public function commitsAction(Project $project, string $stage, Request $request)
	{
		$stageModel = (new StageQuery)
			->filterByProject($project)
			->filterByName($stage)
			->findOne();

		if (null === $stageModel)
		{
			throw $this->createNotFoundException("Stage {$stage} not found");
		}

		$owner    = $project->getOwner();
		$repo     = $project->getRepo();
		$daysBack = $request->query->get('daysBack', 7);

		$commits = $this->get('github.client')->getCommits($owner, $repo, $stageModel->getTrackedBranch(), $daysBack);

		return new JsonResponse($commits);
	}

	/**
	 * @ApiDoc(
	 *     description="Get stage statuses",
	 *     views={"default", "v1"},
	 *     parameters={
	 *         {
	 *             "name": "days_back",
	 *             "dataType": "integer",
	 *             "required": false
	 *         }
	 *     }
	 * )
	 *
	 * @ParamConverter("project", options={"mapping"={"owner":"owner", "repo":"repo"}})
	 */
	public function statusesAction(Project $project, string $stage, Request $request)
	{
		$stageModel = (new StageQuery)
			->filterByProject($project)
			->filterByName($stage)
			->findOne();

		if (null === $stageModel)
		{
			throw $this->createNotFoundException("Stage {$stage} not found");
		}

		$owner    = $project->getOwner();
		$repo     = $project->getRepo();
		$daysBack = $request->query->get('daysBack', 7);

		$statuses = $this->get('github.client')->getStatuses($owner, $repo, $stageModel->getTrackedBranch(), $daysBack);

		return new JsonResponse($statuses);
	}
}
