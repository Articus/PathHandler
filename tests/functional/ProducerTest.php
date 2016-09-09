<?php
namespace Test\PathHandler;

use Articus\PathHandler as PH;
use Articus\DataTransfer as DT;
use Prophecy\Argument;
use Zend\Diactoros\Stream;
use Zend\Expressive\Template\TemplateRendererInterface;

class ProducerTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Test\PathHandler\FunctionalTester
	 */
	protected $tester;

	public function testJsonProducer()
	{
		$producer = new PH\Producer\Json();

		$data = ['test' => 123];
		$result = $producer->assemble($data);
		$this->tester->assertInstanceOf(Stream::class, $result);
		$this->tester->assertEquals('{"test":123}', $result->getContents());

		$exception = new \InvalidArgumentException('Failed to encode array to JSON.');
		$this->tester->expectException($exception, function() use (&$producer)
		{
			$data = ['test' => "\xB1\x31"]; //Invalid UTF-8 string
			$producer->assemble($data);
		});
    }

	public function provideDataForTransfer()
	{
		$object = new Sample\DTO\Transfer();
		$object->test = 123;

		return [
			'for object' => [
				clone $object,
				'{"test":123}',
				1,
				['test' => 'error']
			],
			'for object array' => [
				[clone $object, clone $object],
				'[{"test":123},{"test":123}]',
				2,
				[0 => ['test' => 'error'], 1 => ['test' => 'error']]
			],
			'for mixed array' => [
				[clone $object, 'test', clone $object],
				'[{"test":123},"test",{"test":123}]',
				2,
				[0 => ['test' => 'error'], 2 => ['test' => 'error']]
			],
		];
	}

	/**
	 * @dataProvider provideDataForTransfer
	 */
	public function testTransferProducerSuccessfulTransfer($data, $content, $transferCount, $error)
	{
		$tester = $this->tester;

		$mapperProphecy = $this->prophesize(DT\Mapper\MapperInterface::class);
//		$mapperProphecy->__invoke(Argument::any());

		//Prophecy does not support passing scalar argument by reference :(
		$dtService = $this->createMock(DT\Service::class);
		$dtService
			->expects($this->exactly($transferCount))
			->method('transfer')
			->will($this->returnCallback(
				function($from, &$to, $mapper = null) use (&$tester, &$mapperProphecy)
				{
					$tester->assertInstanceOf(Sample\DTO\Transfer::class, $from);
					$tester->assertEquals(123, $from->test);
					$tester->assertInternalType('array', $to);
					$tester->assertEquals($mapperProphecy->reveal(), $mapper);
					$to['test'] = $from->test;
					return [];
				}
			))
		;
//		$dtServiceProphecy = $this->prophesize(DT\Service::class);
//		$dtServiceProphecy
//			->transfer(Argument::any(), Argument::any(), Argument::any())
//			->will(function($args) use (&$tester, &$mapperProphecy)
//			{
//				$tester->assertInstanceOf(Sample\DTO\Transfer::class, $args[0]);
//				$tester->assertEquals(123, $args[0]->test);
//				$tester->assertInternalType('array', $args[1]);
//				$tester->assertEquals($mapperProphecy->reveal(), $args[2]);
//				$args[1]['test'] = $args[0]->test;
//				return [];
//			})
//			->shouldBeCalledTimes($transferCount)
//		;

		$producer = new PH\Producer\Transfer($dtService, $mapperProphecy->reveal());
		$result = $producer->assemble($data);
		$tester->assertInstanceOf(Stream::class, $result);
		$tester->assertEquals($content, $result->getContents());
	}

	/**
	 * @dataProvider provideDataForTransfer
	 */
	public function testTransferProducerFailedTransfer($data, $content, $transferCount, $error)
	{
		$tester = $this->tester;

		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$dtServiceProphecy
			->transfer(Argument::any(), Argument::any(), Argument::any())
			->willReturn(['test' => 'error'])
			->shouldBeCalledTimes($transferCount)
		;

		$producer = new PH\Producer\Transfer($dtServiceProphecy->reveal());
		$exception = new PH\Exception\UnprocessableEntity($error);
		$tester->expectException($exception, function() use (&$data, &$producer)
		{
			$producer->assemble($data);
		});
	}


	public function testTemplateProducerWithTemplateName()
	{
		$tester = $this->tester;
		$rendererProphecy = $this->prophesize(TemplateRendererInterface::class);
		$rendererProphecy
			->render(Argument::any(), Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals('test', $args[0]);
				$tester->assertInternalType('array', $args[1]);
				$tester->assertEmpty($args[1]);
				return '<test>';
			})
			->shouldBeCalledTimes(1)
		;

		$producer = new PH\Producer\Template($rendererProphecy->reveal());
		$result = $producer->assemble('test');
		$tester->assertInstanceOf(Stream::class, $result);
		$tester->assertEquals('<test>', $result->getContents());
	}

	public function testTemplateProducerWithTemplateNameAndOptions()
	{
		$tester = $this->tester;
		$rendererProphecy = $this->prophesize(TemplateRendererInterface::class);
		$rendererProphecy
			->render(Argument::any(), Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals('test', $args[0]);
				$tester->assertEquals(['test' => 123], $args[1]);
				return '<test test="123">';
			})
			->shouldBeCalledTimes(1)
		;

		$producer = new PH\Producer\Template($rendererProphecy->reveal());
		$result = $producer->assemble(['test', ['test' => 123]]);
		$tester->assertInstanceOf(Stream::class, $result);
		$tester->assertEquals('<test test="123">', $result->getContents());
	}

	public function testTemplateProducerWithTemplateOptions()
	{
		$tester = $this->tester;
		$rendererProphecy = $this->prophesize(TemplateRendererInterface::class);
		$rendererProphecy
			->render(Argument::any(), Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals('error::error', $args[0]);
				$tester->assertEquals(['data' => ['test' => 123]], $args[1]);
				return '<error test="123">';
			})
			->shouldBeCalledTimes(1)
		;

		$producer = new PH\Producer\Template($rendererProphecy->reveal());
		$result = $producer->assemble(['test' => 123]);
		$tester->assertInstanceOf(Stream::class, $result);
		$tester->assertEquals('<error test="123">', $result->getContents());
	}
}