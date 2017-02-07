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

		$this->addRepositoriesNode($rootNode);

		return $treeBuilder;
	}

	protected function addRepositoriesNode(ArrayNodeDefinition $rootNode)
	{
		// @formatter:off
		$rootNode
			->children()
				->arrayNode('repositories')
					->prototype('array')
						->children()
							->scalarNode('name')->isRequired()->end()
							->scalarNode('owner')->isRequired()->end()
							->arrayNode('repository')
								->children()
									->scalarNode('type')->isRequired()->end()
									->scalarNode('uri')->isRequired()->end()
								->end()
							->end()
							->arrayNode('stages')
								->prototype('array')
									->children()
										->scalarNode('name')->isRequired()->end()
										->scalarNode('tracked_branch')->isRequired()->end()
									->end()
								->end()
							->end()
						->end()
					->end()
				->end()
			->end()
		;
		// @formatter:on
	}
}
