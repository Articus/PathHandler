<?php

namespace Articus\PathHandler\Producer;
use Psr\Http\Message\StreamInterface;

/**
 * Interface for producers - services that are used to prepare response body from data returned by handler
 */
interface ProducerInterface
{
    /**
	 * Prepares response body
     * @param mixed $data
     * @return StreamInterface
     */
    public function assemble($data);
}