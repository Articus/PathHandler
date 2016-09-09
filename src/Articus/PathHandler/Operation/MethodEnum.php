<?php
namespace Articus\PathHandler\Operation;

/**
 * Utility enum for storing all method names from operation interfaces
 */
class MethodEnum
{
	const GET = 'handleGet';
	const DELETE = 'handleDelete';
	const PATCH = 'handlePatch';
	const POST = 'handlePost';
	const PUT = 'handlePut';
}