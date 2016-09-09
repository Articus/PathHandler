<?php
namespace Test\PathHandler;

use Articus\PathHandler as PH;
use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProducerFactoryTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Test\PathHandler\FunctionalTester
	 */
	protected $tester;

	protected function _before()
	{
	}

	protected function _after()
	{
	}

	public function testProducerFactoryReturnsTransferProducerWithoutMapper()
	{
		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());
		$containerProphecy->has(DT\Service::class)->willReturn(true);

		$factory = new PH\Producer\Factory();
		$producer = $factory($containerProphecy->reveal(), PH\Producer\Transfer::class);
		$this->tester->assertInstanceOf(PH\Producer\Transfer::class, $producer);
	}

	public function testProducerFactoryReturnsTransferProducerWithMapperSharedService()
	{
		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$mapperProphecy = $this->prophesize(DT\Mapper\MapperInterface::class);

		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());
		$containerProphecy->has(DT\Service::class)->willReturn(true);
		$containerProphecy->get('mapper')->willReturn($mapperProphecy->reveal());
		$containerProphecy->has('mapper')->willReturn(true);

		$factory = new PH\Producer\Factory();
		$producer = $factory($containerProphecy->reveal(), PH\Producer\Transfer::class, ['mapper' => 'mapper']);
		$this->tester->assertInstanceOf(PH\Producer\Transfer::class, $producer);
	}

	public function testProducerFactoryReturnsTransferProducerWithMapperExclusiveService()
	{
		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$mapperProphecy = $this->prophesize(DT\Mapper\MapperInterface::class);

		$containerProphecy = $this->prophesize(ServiceLocatorInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());
		$containerProphecy->has(DT\Service::class)->willReturn(true);
		$containerProphecy->build('mapper', ['test' => 123])->willReturn($mapperProphecy->reveal());
		$containerProphecy->has('mapper')->willReturn(true);

		$factory = new PH\Producer\Factory();
		$producer = $factory(
			$containerProphecy->reveal(),
			PH\Producer\Transfer::class,
			['mapper' => ['name' => 'mapper', 'options' => ['test' => 123]]]
		);
		$this->tester->assertInstanceOf(PH\Producer\Transfer::class, $producer);
	}

	public function testProducerFactoryReturnsTransferProducerWithMapperObject()
	{
		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$mapperProphecy = $this->prophesize(DT\Mapper\MapperInterface::class);

		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());
		$containerProphecy->has(DT\Service::class)->willReturn(true);

		$factory = new PH\Producer\Factory();
		$producer = $factory($containerProphecy->reveal(), PH\Producer\Transfer::class, ['mapper' => $mapperProphecy->reveal()]);
		$this->tester->assertInstanceOf(PH\Producer\Transfer::class, $producer);
	}

	public function testProducerFactoryReturnsTemplateProducer()
	{
		$rendererProphecy = $this->prophesize(TemplateRendererInterface::class);
		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(TemplateRendererInterface::class)->willReturn($rendererProphecy->reveal());
		$containerProphecy->has(TemplateRendererInterface::class)->willReturn(true);

		$factory = new PH\Producer\Factory();
		$producer = $factory($containerProphecy->reveal(), PH\Producer\Template::class);
		$this->tester->assertInstanceOf(PH\Producer\Template::class, $producer);
	}
}