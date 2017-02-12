<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
	public function dashboardAction(): Response
	{
		$response = $this->render('@App/dashboard.html.twig', [

		]);

		return $response;
	}
}
