<?php

declare(strict_types=1);

namespace AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class AppExtension extends ConfigurableExtension
{
	/** {@inheritdoc} */
	protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
	{
	}
}
