<?php

namespace Articus\PathHandler\Producer;

use Zend\Expressive\Template\TemplateRendererInterface;

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
	 * @param TemplateRendererInterface $renderer
	 */
	public function __construct(TemplateRendererInterface $renderer)
	{
		$this->renderer = $renderer;
	}

	/**
	 * @inheritdoc
	 */
	protected function stringify($data)
	{
		$name = null;
		$params = [];

		if (is_array($data) && isset($data[0], $data[1]))
		{
			list($name, $params) = $data;
		}
		else
		{
			$name = $data;
		}

		if (empty($name) || (!is_string($name)))
		{
			//TODO make default template configurable
			$name = 'error::error';
			$params['data'] = $data;
			//throw new \InvalidArgumentException('No template name to render.');
		}
		return $this->renderer->render($name, $params);
	}

}