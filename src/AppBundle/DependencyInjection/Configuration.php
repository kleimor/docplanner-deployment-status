<?php

declare(strict_types=1);

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	/** {@inheritdoc} */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode    = $treeBuilder->root('app');

		$gitHubNode = $rootNode->addDefaultsIfNotSet()->children()->arrayNode('github');

		$this->addCredentialsNode($gitHubNode);
		$this->addRepositoriesNode($gitHubNode);

		return $treeBuilder;
	}

	protected function addCredentialsNode($rootNode)
	{
		// @formatter:off
		$rootNode
			->addDefaultsIfNotSet()
			->children()
				->arrayNode('credentials')
					->prototype('array')
						->children()
							->scalarNode('username')->cannotBeEmpty()->end()
							->scalarNode('password')->cannotBeEmpty()->end()
						->end()
					->end()
				->end()
			->end()
		;
		// @formatter:on
	}

	protected function addRepositoriesNode(ArrayNodeDefinition $rootNode)
	{
		// @formatter:off
		$rootNode
			->addDefaultsIfNotSet()
			->children()
				->arrayNode('subscribed_events')
					->prototype('scalar')->end()
				->end()
			->end()
		;
		// @formatter:on
	}
}
