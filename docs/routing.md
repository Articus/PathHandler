# Routing

PathHandler supports any router that implements `Zend\Expressive\Router\RouterInterface` and expects that:

- it is possible to get instance of this router from container by name `Zend\Expressive\Router\RouterInterface` (like Zend Expressive itself);  
- every successful result of request matching with this router contains matched parameter `handler`. Value of this parameter should be either handler instance or handler service name that can retrieved from handler plugin manager.

You can change both router service name and handler parameters name in middleware configuration:

```YAML
Articus\PathHandler\Middleware:
  handler_attr: my_parameter_for_storing_handler_service_name
  routes: my_router
dependencies:
  factories:
    my_router: My\Router\Factory
```

Library provides two routers out of the box:

- `Articus\PathHandler\Router\FastRouteAnnotation`
- `Articus\PathHandler\Router\TreeConfiguration`

## FastRouteAnnotation

This is a recommended option if you do not have strong preference for something else :)

This router is based on [FastRoute](https://packagist.org/packages/nikic/fast-route) and allows to declare paths via annotation `Articus\PathHandler\Annotation\Route` like that: 

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Operation\PostInterface;

/**
 * @PHA\Route(pattern="/entity/{id:\d+}", defaults={"test":123})
 */
class Handler implements PostInterface
{/* some code */}
```

Route `pattern` should be filled according [FastRoute rules](https://github.com/nikic/FastRoute/blob/master/README.md). Matched parameters are calculated as `array_merge (['handler' => <handler class FQN>], <defaults>, <parameteres parsed from URI>)`. If you set custom `handler_attr` in middleware configuration, you need to repeat it in router configuration:
 
```YAML
Articus\PathHandler\Middleware:
  handler_attr: my_parameter_for_storing_handler_service_name
Articus\PathHandler\Router\FastRouteAnnotation:
  handler_attr: my_parameter_for_storing_handler_service_name
```

If you set multiple routes for handler they will be checked in the order they appear in annotations:

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Annotation as PHA;

/**
 * @PHA\Route(pattern="/entity/0")
 * @PHA\Route(pattern="/entity/{id:\d+}")
 */
class Handler implements PostInterface
{/* some code */}
```

Or you can adjust this order with priority setting (default value is 1). Routes with higher priority will be executed earlier:

```PHP
```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Annotation as PHA;

/**
 * @PHA\Route(pattern="/entity/{id:\d+}")
 * @PHA\Route(pattern="/entity/0", priority=10)
 */
class Handler implements PostInterface
{/* some code */}
```

This router can created with factory `Articus\PathHandler\Router\Factory\FastRouteAnnotation`.
 
## TreeConfiguration

This router is based on [Zend Router](https://packagist.org/packages/zendframework/zend-router) and allows to declare paths via tree-like configuration:

```YAML
Articus\PathHandler\Router\TreeConfiguration:
  routes:
    entity:
      type: Literal
      options:
        route: /entity
        defaults:
          handler: My\Handler
```

Configuration format is defined by `Zend\Router\Http\TreeRouteStack::factory`, check [its documentation](https://docs.zendframework.com/zend-router/routing/#treeroutestack) for details.

This router can created with factory `Articus\PathHandler\Router\Factory\TreeConfiguration`.