<?php
namespace Test\PathHandler;

use Articus\PathHandler as PH;
use Zend\Diactoros\Stream;

class ConsumerTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Test\PathHandler\FunctionalTester
	 */
	protected $tester;

	public function testInternalConsumer()
	{
		$consumer = new PH\Consumer\Internal();

		$body = new Stream('php://memory', 'wb+');
		$body->write('test:payload');
		$body->rewind();
		$parsedBody = ['test' => 'payload'];

		$result = $consumer->parse($body, $parsedBody, '', []);
		$this->tester->assertEquals($parsedBody, $result);
	}

	public function testJsonConsumer()
	{
		$consumer = new PH\Consumer\Json();

		$body = new Stream('php://memory', 'wb+');

		$body->write('{"test":"payload"}');
		$body->rewind();
		$result = $consumer->parse($body, null, '', []);
		$this->tester->assertEquals(['test' => 'payload'], $result);

		$body->write('{');
		$body->rewind();
		$exception = new PH\Exception\BadRequest('Malformed JSON');
		$this->tester->expectException($exception, function() use (&$consumer, &$body)
		{
			$consumer->parse($body, null, '', []);
		});
	}
}