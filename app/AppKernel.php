<?php

declare(strict_types=1);

use Monolog\ErrorHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
	/** @var null|ErrorHandler */
	protected static $errorHandler;

	/** {@inheritdoc} */
	public function registerBundles()
	{
		$bundles = [
			new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new Symfony\Bundle\SecurityBundle\SecurityBundle(),
			new Symfony\Bundle\TwigBundle\TwigBundle(),
			new Symfony\Bundle\MonologBundle\MonologBundle(),
			new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
			new Lopi\Bundle\PusherBundle\LopiPusherBundle(),
			new Propel\Bundle\PropelBundle\PropelBundle(),
			new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
			new AppBundle\AppBundle(),
		];

		if (in_array($this->getEnvironment(), ['dev', 'test'], true))
		{
			$bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
			$bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
			$bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
			$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
		}

		return $bundles;
	}

	/** {@inheritdoc} */
	public function boot()
	{
		$ret = parent::boot();

		if (null === self::$errorHandler)
		{
			$container = $this->getContainer();
			$logger    = $container->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE);
			if ($logger instanceof LoggerInterface)
			{
				self::$errorHandler = new ErrorHandler($logger);
				self::$errorHandler->registerErrorHandler();
				self::$errorHandler->registerExceptionHandler(LogLevel::ERROR);
				self::$errorHandler->registerFatalHandler(LogLevel::ALERT);
			}
		}

		return $ret;
	}

	/** {@inheritdoc} */
	public function getRootDir()
	{
		return __DIR__;
	}

	/** {@inheritdoc} */
	public function getCacheDir()
	{
		return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
	}

	/** {@inheritdoc} */
	public function getLogDir()
	{
		return dirname(__DIR__) . '/var/logs';
	}

	/** {@inheritdoc} */
	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		$loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
	}
}
