<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use Mezzio\Response\ServerRequestErrorResponseGenerator;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Simple producer that uses provided data to render template
 */
class Template extends AbstractProducer
{
	/**
	 * @var TemplateRendererInterface
	 */
	protected $renderer;

	/**
	 * @param callable $streamFactory
	 * @param TemplateRendererInterface $renderer
	 */
	public function __construct(callable $streamFactory, TemplateRendererInterface $renderer)
	{
		parent::__construct($streamFactory);
		$this->renderer = $renderer;
	}

	/**
	 * @inheritdoc
	 */
	protected function stringify($data): string
	{
		$name = null;
		$params = [];

		if (\is_array($data) && isset($data[0], $data[1]))
		{
			[$name, $params] = $data;
		}
		else
		{
			$name = $data;
		}

		if (empty($name) || (!\is_string($name)))
		{
			//TODO make default template configurable
			$name = ServerRequestErrorResponseGenerator::TEMPLATE_DEFAULT;
			$params['data'] = $data;
		}

		return $this->renderer->render($name, $params);
	}
}