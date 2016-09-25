# Path Handler

[![Travis](https://travis-ci.org/Articus/PathHandler.svg?branch=master)](https://travis-ci.org/Articus/PathHandler)
[![Coveralls](https://coveralls.io/repos/github/Articus/PathHandler/badge.svg?branch=master)](https://coveralls.io/github/Articus/PathHandler?branch=master)
[![Codacy](https://api.codacy.com/project/badge/Grade/02dc4cfb69e34079ab380593fe5f4f70)](https://www.codacy.com/app/articusw/PathHandler?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Articus/PathHandler&amp;utm_campaign=Badge_Grade)

This library provides a replacement for default routing and dispatch middleware from [Zend Expressive](http://zendframework.github.io/zend-expressive/) and should considerably simplify creating APIs with this nice framework. The idea is to split request processing into phases that should be familiar to anyone who used to write [Swagger](http://swagger.io/) specs:
   
- routing - determine what operation should you perform for specified request path
- consuming - parse request body
- attributing - calculate set of request attributes that you need to perform operation from raw request data (it can be any auxiliary actions like checking authentication and getting current user, validating data and making DTO from it, retrieving entity from DB by id from request parameters and etc)
- handling - perform requested operation and get some result
- producing - prepare response body and headers using operation result

## How to install?

Just add `"articus/path-handler": "*"` to your [composer.json](https://getcomposer.org/doc/04-schema.md#require).

## How to use?

First of all you need to register a new middleware in your application and add it to middleware pipeline (example is in YAML just for readability):

```YAML
dependencies:
  factories:
    Articus\PathHandler\Middleware: Articus\PathHandler\MiddlewareFactory
middleware_pipeline:
  api:
    middleware: Articus\PathHandler\Middleware
```

Next you need to configure metadata cache storage that will be used by middleware:

```YAML
path_handler:
  # Configure dedicated Zend Cache Storage (see Zend\Cache\StorageFactory) 
  metadata_cache:
    adapter: filesystem
    options:
      cache_dir: data/PathHandler
      namespace: ph
    plugins:
      serializer:
        serializer: phpserialize
  # ... or use existing service inside container
  #metadata_cache: MyMetadataCacheStorage
```

Next you need to declare some **paths** that will be used to call your API:

```YAML
path_handler:
  # Configure dedicated Zend Router (see Zend\Router\Http\TreeRouteStack::factory) 
  routes:
    routes:
      entity:
        type: Literal
        options:
          route: /entity
  # ... or use existing Zend\Expressive\Router\RouterInterface service inside container
  #routes: MyRouter
```

Finally you need to declare **handlers**. Each handler is a set of all **operations** that can be performed when your path is accessed with distinct HTTP methods. To do this you just need to make a class implementing at least one of the interfaces from `Articus\PathHandler\Operation\ `, decorate its methods with special annotations:

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Exception;
use Articus\PathHandler\Operation\PostInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        return ['result' => 'success'];
    }
}
```

... and register this class in configuration:

```YAML
path_handler:
  routes:
    routes:
      entity:
        type: Literal
        options:
          route: /entity
          defaults:
            #Add a routing parameter to link it with path
            handler: Handler
  #Add entry in handler plugin manager 
  handlers:
    invokables:
      Handler: My\Handler 
```

For more details about consuming check [this doc](docs/consuming.md).

For more details about attributing check [this doc](docs/attributing.md).

For more details about producing check [this doc](docs/producing.md).

# Enjoy!
I hope this library will be useful for someone except me. 
Currently it is only the initial release. It is used for production purposes but it lacks lots of refinement, especially in terms of tests and documentation. 

If you have any suggestions, advices, questions or fixes feel free to submit issue or pull request.
