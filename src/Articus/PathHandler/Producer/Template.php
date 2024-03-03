<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use function is_array;
use function is_string;

/**
 * Simple producer that uses provided data to render template
 * @see Options\Template for details
 */
class Template implements ProducerInterface
{
	public function __construct(
		protected StreamFactoryInterface $streamFactory,
		protected TemplateRendererInterface $renderer,
		protected string $defaultTemplate
	)
	{
	}

	/**
	 * @inheritdoc
	 */
	public function assemble(mixed $data): null|StreamInterface
	{
		$name = null;
		$params = [];

		if (is_array($data) && isset($data[0], $data[1]))
		{
			[$name, $params] = $data;
		}
		else
		{
			$name = $data;
		}

		if (empty($name) || (!is_string($name)))
		{
			$name = $this->defaultTemplate;
			$params['data'] = $data;
		}

		$content = $this->renderer->render($name, $params);
		return $this->streamFactory->createStream($content);
	}
}
