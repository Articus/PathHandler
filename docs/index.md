# Path Handler

This library considerably simplifies API development with [Mezzio](https://github.com/mezzio/mezzio) by reducing amount of boilerplate code you have to write for each API operation. The idea is to provide a more convenient way to deal with:

- routing - routes for all operations are registered automatically
- consuming - each operation may have unique algorithm to parse request body according its content type
- attributing (as in [PSR-7 request attributes](https://www.php-fig.org/psr/psr-7/#15-server-side-requests)) - each operation may have its own set of request attributes calculated from raw request data (like current user information insteadof authentication header, validated DTO insteadof form value array, entity object insteadof query parameter with its id and so on)
- producing - each operation may have unique algorithm to prepare response body from operation result according media type accepted by client

So you can focus on handling your API operations and spend less time on writing auxiliary code for request processing.

## How to install?

Just add `"articus/path-handler"` to your [composer.json](https://getcomposer.org/doc/04-schema.md#require) and check [packages suggested by the library](https://getcomposer.org/doc/04-schema.md#suggest) for extra dependencies of optional components you want to use.  

## How to use?

First of all you need a project with [Mezzio](https://github.com/mezzio/mezzio) application. For example, you can generate one with [this installer](https://github.com/mezzio/mezzio-skeleton).  

Next you need to declare **handlers**. Each handler is a set of all **operations** that can be performed when some **path** of your API is accessed with distinct HTTP methods. Any class can be a handler, you just need to annotate it with special PHP attributes:

```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Articus\PathHandler\Exception;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route('/entity')] //This is how you set path for handler operations
class Handler
{
    #[PHA\Post()] //This is how you declare HTTP method of the operation
    #[PHA\Consumer('application/json', 'Json')] //This is how you consume request body
    #[PHA\Attribute('Transfer', ['type'=>'My\DTO','object_attr'=>'dto','error_attr'=>'errors'])] //This is how you attribute request
    #[PHA\Producer('application/json', 'Json')] //This is how you produce response body from returned value
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

Finally, you need to configure special factory for router service. Here is a sample configuration for [Laminas Service Manager](https://docs.laminas.dev/laminas-servicemanager/) (example is in YAML just for readability):

```YAML
dependencies:
  factories:
    Mezzio\Router\RouterInterface: Articus\PathHandler\RouteInjectionFactory
    Articus\PathHandler\MetadataProviderInterface: Articus\PathHandler\MetadataProvider\Factory\PhpAttribute
    Articus\PathHandler\Handler\PluginManager: [Articus\PluginManager\Factory\Laminas, Articus\PathHandler\Handler\PluginManager]
    Articus\PathHandler\Consumer\PluginManager: Articus\PathHandler\Consumer\Factory\PluginManager
    Articus\PathHandler\Attribute\PluginManager: Articus\PathHandler\Attribute\Factory\PluginManager
    Articus\PathHandler\Producer\PluginManager: Articus\PathHandler\Producer\Factory\PluginManager

Articus\PathHandler\RouteInjectionFactory:
  paths:
    '':
    # List of your handlers   
    - My\Handler
# Configuration for handler plugin manager - sub-container dedicated for handlers
Articus\PathHandler\Handler\PluginManager:
  factories:
    My\Handler: My\HandlerFactory
```

## Production configuration

In production environment you may want to activate persistent handler metadata cache via configuration:

```YAML
# Cache metadata in single file data/cache/php-attribute-metadata.php
Articus\PathHandler\MetadataProvider\PhpAttribute:
  cache:
    directory: data/cache
```

If you use default router you may also want to activate persistent routing table cache:

```YAML
# Cache routing table in single file data/cache/fast-route.php
Articus\PathHandler\RouteInjectionFactory:
  router:
    cache:
      directory: data/cache
```

If your application uses some [PSR-16 implementation](https://packagist.org/providers/psr/simple-cache-implementation), library can utilize it insteadof simple file caching:
```YAML
dependencies:
  factories:
    my_psr16_implementation: My\PSR16ImplementationFactory

Articus\PathHandler\MetadataProvider\PhpAttribute:
  cache: my_psr16_implementation

Articus\PathHandler\RouteInjectionFactory:
  router:
    cache: my_psr16_implementation
```
