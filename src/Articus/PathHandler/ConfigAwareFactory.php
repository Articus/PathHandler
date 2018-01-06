<?php
namespace Articus\PathHandler;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Base class for factories that require configuration from Config service
 */
abstract class ConfigAwareFactory implements FactoryInterface
{
	/**
	 * Key inside Config service
	 * @var string
	 */
	protected $configKey;

	/**
	 * Factory constructor.
	 */
	public function __construct($configKey)
	{
		$this->configKey = $configKey;
	}

	/**
	 * Small hack to simplify configuration when you want to pass custom config key but do not want to create extra class or anonymous function.
	 * So for example in your configuration YAML file you can use:
	 * dependencies:
	 *   factories:
	 *     my_service: [My\Service\ConfigAwareFactory, my_service_config]
	 * my_service_config:
	 *   parameter: value
	 */
	public static function __callStatic($name, array $arguments)
	{
		if (count($arguments) < 3)
		{
			throw new \InvalidArgumentException(sprintf(
				'To invoke %s with custom configuration key statically 3 arguments are required: container, service name and options.',
				static::class
			));
		}
		return (new static($name))->__invoke($arguments[0], $arguments[1], $arguments[2]);
	}

	/**
	 * Extracts service configuration from container
	 * @param ContainerInterface $container
	 * @return array
	 */
	protected function getServiceConfig(ContainerInterface $container)
	{
		$config = $container->get('config');
		return (isset($config[$this->configKey])? $config[$this->configKey] : []);
	}
}