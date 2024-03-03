<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Options;

use Articus\PathHandler\Producer;

class Transfer
{
	/**
	 * Name of the subset that should be used to transfer data
	 */
	public string $subset = '';
	/**
	 * Name for producer plugin manager to get producer that will receive transferred data
	 */
	public string $producerName = Producer\Json::class;
	/**
	 * Options for producer plugin manager to get producer that will receive transferred data
	 */
	public array $producerOptions = [];

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'subset':
					$this->subset = $value;
					break;
				case 'producerName':
				case 'producer_name':
				case 'name':
					$this->producerName = $value;
					break;
				case 'producerOptions':
				case 'producer_options':
				case 'options':
					$this->producerOptions = $value;
					break;
			}
		}
	}
}
