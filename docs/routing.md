# Routing

PathHandler provides several neat improvements for [standard Zend Expressive routing registration routine](https://docs.zendframework.com/zend-expressive/v3/features/router/intro/).

## Route path prefixes

You can easily add common path prefix for a group of handlers via router factory configuration:

```YAML
Articus\PathHandler\RouteInjection\Factory:
  paths:
    '':
    # List of handlers that should not be prefixed   
    - My\Handler1
    - My\Handler2
    '/v1':
    # List of handlers which path should be prefixed with /v1   
    - My\V1\Handler1
    - My\V1\Handler2
```

It is useful for prefixes that do not affect handler behaviour, for example API version number.

## Route path declaration

Each handler should have at least one path declaration. Each path declaration may have default values for parameters that will be available after matching and unique name to simplify URI generation. You may declare several paths for single handler. Corresponding routes will be registered in the order they appear in annotations or based on their priority. Check `Articus\PathHandler\Annotation\Route` for details.

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Exception;

/**
 * @PHA\Route(pattern="/some/path")
 * @PHA\Route(pattern="/another/path", name="another_path_name")
 * @PHA\Route(pattern="/another/path", defaults={"some_param": 123, "another_param": "another param value"})
 */
class Handler
{
//...
}
```

Path pattern syntax depends on router you choose. Default one is `Articus\PathHandler\Router\FastRoute` based on [FastRoute](https://packagist.org/packages/nikic/fast-route). You can switch to your favourite router implementation via router factory configuration:

```YAML
dependencies:
  factories:
    Zend\Expressive\Router\RouterInterface: Articus\PathHandler\RouteInjection\Factory
    my_router: My\RouterFactory

Articus\PathHandler\RouteInjection\Factory:
  router: my_router
```

## Route HTTP method declaration

Each handler should have at least one class method with HTTP method declaration. There are "shortcuts" for GET, POST, PUT, PATCH and DELETE. Any other HTTP method can be declared with generic `Articus\PathHandler\Annotation\HttpMethod` annotation. Class method may have several HTTP method declarations. You may even create two handlers with same path but mark their class methods to handle different HTTP methods.

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Route(pattern="/entity/{id}")
 */
class Handler
{
    /**
     * @PHA\HttpMethod("HEAD")
     * @PHA\Get()
     */
    public function read(ServerRequestInterface $request)
    {
        //...
    }

    /**
     * @PHA\Put()
     * @PHA\Patch()
     */
    public function update(ServerRequestInterface $request)
    {
        //...
    }
}

/**
 * @PHA\Route(pattern="/entity/{id}")
 */
class DeleteHandler
{
    /**
     * @PHA\Delete()
     */
    public function delete(ServerRequestInterface $request)
    {
        //...
    }
}
```
