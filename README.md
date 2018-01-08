# Path Handler

[![Travis](https://travis-ci.org/Articus/PathHandler.svg?branch=master)](https://travis-ci.org/Articus/PathHandler)
[![Documentation](https://readthedocs.org/projects/pathhandler/badge/?version=latest)](http://pathhandler.readthedocs.io/en/latest/?badge=latest)
[![Coveralls](https://coveralls.io/repos/github/Articus/PathHandler/badge.svg?branch=master)](https://coveralls.io/github/Articus/PathHandler?branch=master)
[![Codacy](https://api.codacy.com/project/badge/Grade/02dc4cfb69e34079ab380593fe5f4f70)](https://www.codacy.com/app/articusw/PathHandler?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Articus/PathHandler&amp;utm_campaign=Badge_Grade)

This library provides a replacement for default routing and dispatch middleware from [Zend Expressive](http://zendframework.github.io/zend-expressive/) and should considerably simplify creating APIs with this nice framework. The idea is to split request processing into phases that should be familiar to anyone who used to write [Swagger](http://swagger.io/) specs:
   
- routing - determine what operation should you perform for specified request path
- consuming - parse request body
- attributing - calculate set of request attributes that you need to perform operation from raw request data (it can be any auxiliary actions like checking authentication and getting current user, validating data and making DTO from it, retrieving entity from DB by id from request parameters and etc)
- handling - perform requested operation and get some result
- producing - prepare response body and headers using operation result

## Quick start

Just write [Swagger specification](https://swagger.io/specification/) for your future API and use [Swagger Codegen](https://swagger.io/swagger-codegen/) to generate `ze-ph` server. Both steps can be easily done in your browser with [Swagger Editor](http://editor.swagger.io/).   

## How to install?

Just add `"articus/path-handler": "*"` to your [composer.json](https://getcomposer.org/doc/04-schema.md#require) and check [packages suggested by the library](https://getcomposer.org/doc/04-schema.md#suggest) for extra dependencies of optional components you want to use.  

## How to use?

First of all you need to declare **handlers**. Each handler is a set of all **operations** that can be performed when some **path** of your API is accessed with distinct HTTP methods. To do this you just need to make a class implementing at least one of the interfaces from `Articus\PathHandler\Operation\ ` and decorate its methods with special annotations:

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Exception;
use Articus\PathHandler\Operation\PostInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This is how you set path for handler operations
 * @PHA\Route(pattern="/entity")
 */
class Handler implements PostInterface
{
    /**
     * This is how you consume request body
     * @PHA\Consumer(name="Json", mediaType="application/json")
     * This is how you attribute request
     * @PHA\Attribute(name="Transfer", options={"type":"My\DTO","objectAttr":"dto","errorAttr":"errors"})
     * This is how you produce response body from returned value
     * @PHA\Producer(name="Json", mediaType="application/json")
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $errors = $request->getAttribute('errors');
        if (!empty($errors))
        {
            //This is how you can return non-200 responses
            throw new Exception\UnprocessableEntity($errors);
        }
        /* @var My\DTO $dto */
        $dto = $request->getAttribute('dto');
        return $dto;
    }
}
```

Next you need to configure router service (example is in YAML just for readability):

```YAML
dependencies:
  factories:
    Zend\Expressive\Router\RouterInterface: Articus\PathHandler\Router\FastRouteAnnotationFactory
Articus\PathHandler\Router\FastRouteAnnotation:
  # Storage for routing metadata
  metadata_cache:
    adapter: filesystem
    options:
      cache_dir: data/FastRouteAnnotation
      namespace: fra
    plugins:
      serializer:
        serializer: phpserialize
  # List of all your handlers
  handlers:
    - My\Handler
```

Next you need to configure new middleware:

```YAML
Articus\PathHandler\Middleware:
  # Storage for middleware metadata
  metadata_cache:
    adapter: filesystem
    options:
      cache_dir: data/PathHandler
      namespace: ph
    plugins:
      serializer:
        serializer: phpserialize
  # Configuration for handler plugin manager - sub-container dedicated for handlers
  handlers:
    factories:
      My\Handler: My\HandlerFactory
```

Finally you need to register new middleware and add it to middleware pipeline:

```YAML
dependencies:
  factories:
    Articus\PathHandler\Middleware: Articus\PathHandler\MiddlewareFactory
middleware_pipeline:
  api:
    middleware: Articus\PathHandler\Middleware
```

For more details check [documentation](http://pathhandler.readthedocs.io/en/latest/).

# Enjoy!
I hope this library will be useful for someone except me. 
Currently it is only the initial release. It is used for production purposes but it lacks lots of refinement, especially in terms of tests and documentation. 

If you have any suggestions, advices, questions or fixes feel free to submit issue or pull request.
