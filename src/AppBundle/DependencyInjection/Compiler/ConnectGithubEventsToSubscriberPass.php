<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ConnectGithubEventsToSubscriberPass implements CompilerPassInterface
{
	/** {@inheritdoc} */
	public function process(ContainerBuilder $container)
	{
		$subscribedEvents = $container->getParameter('app.github.subscribed_events');
		$githubSubscriber = $container->findDefinition('subscriber.github');
		foreach ($subscribedEvents as $subscribedEvent)
		{
			$githubSubscriber->addTag('kernel.event_listener', [
				'event'    => "github.{$subscribedEvent}",
				'method'   => 'onGithubEvent',
				'priority' => -100,
			]);
		}
	}
}
