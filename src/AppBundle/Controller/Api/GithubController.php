<?php

declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Event\GitHub\GitHubEventFactory;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GithubController extends Controller
{
	const HEADER_EVENT     = 'X-GitHub-Event';
	const HEADER_DELIVERY  = 'X-GitHub-Delivery';
	const HEADER_SIGNATURE = 'X-Hub-Signature';

	/**
	 * @ApiDoc(
	 *     description="GitHub webhook callback",
	 *     tags={"internal"},
	 *     views={"default","v1"}
	 * )
	 */
	public function callbackAction(Request $request)
	{
		foreach ([
			self::HEADER_EVENT,
			self::HEADER_DELIVERY,
			self::HEADER_SIGNATURE,
		] as $header)
		{
			if (!$request->headers->has($header))
			{
				return new JsonResponse([
					'reason' => sprintf('%s header not found', $header),
				], JsonResponse::HTTP_BAD_REQUEST);
			}
		}

		$subscribedEvents = $this->getParameter('app.github.subscribed_events');

		$eventType = $request->headers->get(self::HEADER_EVENT);
		if (!in_array($eventType, $subscribedEvents, true))
		{
			return new JsonResponse([
				'reason' => 'Event not subscribed',
			], JsonResponse::HTTP_BAD_REQUEST);
		}

		$signature = $request->headers->get(self::HEADER_SIGNATURE, '');
		if (!$this->verifySignature($signature))
		{
			return new JsonResponse([
				'reason' => 'Signature validation failed',
			], JsonResponse::HTTP_BAD_REQUEST);
		}

		$payload = json_decode($request->getContent(false), true);
		if (!is_array($payload))
		{
			return new JsonResponse([
				'reason' => 'Request payload is not JSON',
			], JsonResponse::HTTP_BAD_REQUEST);
		}

		try
		{
			$event = GitHubEventFactory::createEvent($eventType, $payload);
		}
		catch (\InvalidArgumentException $invalidArgumentException)
		{
			return new JsonResponse([
				'reason' => $invalidArgumentException->getMessage(),
			], JsonResponse::HTTP_BAD_REQUEST);
		}

		$this->get('event_dispatcher')->dispatch($event::getEventName(), $event);

		return new Response(null, Response::HTTP_NO_CONTENT);
	}

	protected function verifySignature(string $signature): bool
	{
		return true;
	}
}
