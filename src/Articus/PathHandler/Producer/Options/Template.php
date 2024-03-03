<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Options;

use Mezzio\Response\ServerRequestErrorResponseGenerator;
use Mezzio\Template\TemplateRendererInterface;

class Template
{
	/**
	 * Service name for template renderer implementation inside container
	 */
	public string $templateRendererServiceName = TemplateRendererInterface::class;

	/**
	 * Name of the template that should be used if data from handler does not contain template name (mostly for error rendering)
	 */
	public string $defaultTemplate = ServerRequestErrorResponseGenerator::TEMPLATE_DEFAULT;

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'templateRendererServiceName':
				case 'template_renderer_service_name':
				case 'templateRenderer':
				case 'template_renderer':
				case 'renderer':
					$this->templateRendererServiceName = $value;
					break;
				case 'defaultTemplate':
				case 'default_template':
					$this->defaultTemplate = $value;
					break;
			}
		}
	}
}
