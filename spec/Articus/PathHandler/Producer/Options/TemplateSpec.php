<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Options;

use PhpSpec\ObjectBehavior;

class TemplateSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$rendererName = 'test_renderer';
		$defaultTemplate = 'test_template';
		$options = [
			'templateRendererServiceName' => $rendererName,
			'defaultTemplate' => $defaultTemplate,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('templateRendererServiceName', $rendererName);
		$this->shouldHaveProperty('defaultTemplate', $defaultTemplate);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$rendererName = 'test_renderer';
		$defaultTemplate = 'test_template';
		$options = [
			'template_renderer_service_name' => $rendererName,
			'default_template' => $defaultTemplate,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('templateRendererServiceName', $rendererName);
		$this->shouldHaveProperty('defaultTemplate', $defaultTemplate);
	}
}
