# Path Handler

[![Travis](https://travis-ci.org/Articus/PathHandler.svg?branch=master)](https://travis-ci.org/Articus/PathHandler)
[![Documentation](https://readthedocs.org/projects/pathhandler/badge/?version=latest)](http://pathhandler.readthedocs.io/en/latest/?badge=latest)
[![Coveralls](https://coveralls.io/repos/github/Articus/PathHandler/badge.svg?branch=master)](https://coveralls.io/github/Articus/PathHandler?branch=master)
[![Codacy](https://api.codacy.com/project/badge/Grade/02dc4cfb69e34079ab380593fe5f4f70)](https://www.codacy.com/app/articusw/PathHandler?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Articus/PathHandler&amp;utm_campaign=Badge_Grade)


This library considerably simplifies API development with [Zend Expressive](http://zendframework.github.io/zend-expressive/) by reducing amount of boilerplate code you have to write for each API operation. The idea is to provide a more convenient way to deal with:

- routing - routes for all operations are registered automatically
- consuming - each operation may have unique algorithm to parse request body according its content type
- attributing (as in [PSR-7 request attributes](https://www.php-fig.org/psr/psr-7/#15-server-side-requests)) - each operation may have its own set of request attributes calculated from raw request data (like current user information insteadof authentication header, validated DTO insteadof form value array, entity object insteadof query parameter with its id and so on)
- producing - each operation may have unique algorithm to prepare response body from operation result according media type accepted by client

So you can focus on handling your API operations and spend less time on writing auxiliary code for request processing.

## Quick start

Just write [OpenAPI Specification](https://swagger.io/specification/) for your future API and use [OpenAPI Codegen](https://openapi-generator.tech/) to generate `php-ze-ph` server.  Check a sample of what you get [here](https://github.com/OpenAPITools/openapi-generator/tree/master/samples/server/openapi3/petstore/php-ze-ph).

## How to install?

Just add `"articus/path-handler": "*"` to your [composer.json](https://getcomposer.org/doc/04-schema.md#require) and check [packages suggested by the library](https://getcomposer.org/doc/04-schema.md#suggest) for extra dependencies of optional components you want to use.  

## How to use?

First of all you need a project with Zend Expressive application. For example you can generate one with [this installer](https://github.com/zendframework/zend-expressive-skeleton).  

Next you need to declare **handlers**. Each handler is a set of all **operations** that can be performed when some **path** of your API is accessed with distinct HTTP methods. Any class can be a handler, you just need to decorate it with special annotations:

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This is how you set path for handler operations
 * @PHA\Route(pattern="/entity")
 */
class Handler
{
    /**
     * This is how you declare HTTP method of the operation
     * @PHA\Post()
     * This is how you consume request body
     * @PHA\Consumer(name="Json", mediaType="application/json")
     * This is how you attribute request
     * @PHA\Attribute(name="Transfer", options={"type":"My\DTO","objectAttr":"dto","errorAttr":"errors"})
     * This is how you produce response body from returned value
     * @PHA\Producer(name="Json", mediaType="application/json")
     */
    public function handlePost(ServerRequestInterface $request): \My\DTO
    {
        $errors = $request->getAttribute('errors');
        if (!empty($errors))
        {
            //This is how you can return non-200 responses
            throw new Exception\UnprocessableEntity($errors);
        }
        /* @var \My\DTO $dto */
        $dto = $request->getAttribute('dto');
        return $dto;
    }
}
```

Finally you need to configure special factory for router service (example is in YAML just for readability):

```YAML
dependencies:
  factories:
    Zend\Expressive\Router\RouterInterface: Articus\PathHandler\RouteInjection\Factory

Articus\PathHandler\RouteInjection\Factory:
  paths:
    '':
    # List of your handlers   
    - My\Handler
  # Configuration for handler plugin manager - sub-container dedicated for handlers
  handlers:
    factories:
      My\Handler: My\HandlerFactory
```

For more details check [documentation](http://pathhandler.readthedocs.io/en/latest/).

# Enjoy!
I hope this library will be useful for someone except me. 
It is used for production purposes but it lacks lots of refinement, especially in terms of tests and documentation. 

If you have any suggestions, advices, questions or fixes feel free to submit issue or pull request.
