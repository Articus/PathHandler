# Attributing

To attribute request you need an **attribute** - class that implements `Articus\PathHandler\Attribute\AttributeInterface`:

```PHP
namespace My;

use Articus\PathHandler\Attribute\AttributeInterface;
use Psr\Http\Message\ServerRequestInterface;

class Attribute implements AttributeInterface
{
    public function __invoke(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute('some', 'thing'); 
    }
}
```
 
... and is registered in configuration:
 
```YAML
#Add entry in attribute plugin manager 
Articus\PathHandler\Attribute\PluginManager:
  invokables:
    MyAttribute: My\Attribute 
```

To use attribute for operation in your handler you just need to annotate operation method:

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Route(pattern="/entity")
 */
class Handler
{
    /**
     * @PHA\Post()
     * @PHA\Attribute(name="MyAttribute")
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some');
    }
}
```
```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
class Handler
{
    #[PHA\Post()]
    #[PHA\Attribute("MyAttribute")]
    public function handlePost(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some');
    }
}
```

It is possible to pass configuration options to your attribute factory:

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Route(pattern="/entity")
 */
class Handler
{
    /**
     * @PHA\Post()
     * @PHA\Attribute(name="MyAttribute", options={"key":"value"})
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some'); 
    }
}
```
```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
class Handler
{
    #[PHA\Post()]
    #[PHA\Attribute("MyAttribute", ["key" => "value"])]
    public function handlePost(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some'); 
    }
}
```

If all operations in your handler need same attribute you can just annotate handler class insteadof annotating each method: 

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Route(pattern="/entity")
 * @PHA\Attribute(name="MyAttribute")
 */
class Handler
{
    /**
     * @PHA\Post()
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some'); 
    }
    /**
     * @PHA\Patch()
     */
    public function handlePatch(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some'); 
    }
}
```
```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
#[PHA\Attribute("MyAttribute")]
class Handler
{
    #[PHA\Post()]
    public function handlePost(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some'); 
    }
    #[PHA\Patch()]
    public function handlePatch(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some'); 
    }
}
```

If you set multiple attributes for operation they will be invoked in the same order they appear in annotations:

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Route(pattern="/entity")
 * @PHA\Attribute(name="First")
 * @PHA\Attribute(name="Second")
 */
class Handler
{
    /**
     * @PHA\Post()
     * @PHA\Attribute(name="Third")
     * @PHA\Attribute(name="Fourth")
     */
    public function handlePost(ServerRequestInterface $request)
    {
    }
}
```
```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
#[PHA\Attribute("First")]
#[PHA\Attribute("Second")]
class Handler
{
    #[PHA\Post()]
    #[PHA\Attribute("Third")]
    #[PHA\Attribute("Fourth")]
    public function handlePost(ServerRequestInterface $request)
    {
    }
}
```

Or you can adjust this order with priority setting (default value is 1). Attributes with higher priority will be executed earlier:

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Route(pattern="/entity")
 */
class Handler
{
    /**
     * @PHA\Post()
     * @PHA\Attribute(name="Second")
     * @PHA\Attribute(name="First", priority=10)
     */
    public function handlePost(ServerRequestInterface $request)
    {
    }
}
```
```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
class Handler
{
    #[PHA\Post()]
    #[PHA\Attribute("Second")]
    #[PHA\Attribute("First", priority: 10)]
    public function handlePost(ServerRequestInterface $request)
    {
    }
}
```

## Build-in attributes
Library provides two attributes out of the box:

- `IdentifiableValueLoad` that uses [Data Transfer library](https://github.com/Articus/DataTransfer) to load some value by its identifier stored in request attribute
- `Transfer` that uses [Data Transfer library](https://github.com/Articus/DataTransfer) to construct DTO and fill it with request data only if this data is valid.

### `IdentifiableValueLoad` usage

Add `Articus\DataTransfer\IdentifiableValueLoader` service inside your container, for example with a factory like:

```PHP
namespace My;

use Articus\DataTransfer\IdentifiableValueLoader;
use Psr\Container\ContainerInterface;

class LoaderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new IdentifiableValueLoader([
            "entity_type" => [
                static function (EntityClass $value) {
                    return $value->getId();//...or any other ay to get id from EntityClass instance
                },
                static function (array $ids) {
                    /** @var EntityClass[] $result */
                    $result = []
                    // load EntityClass instances for specified ids from database, external service, etc...
                    return $result;
                }
            ]           
        ])
    }
}
```

And then just add attribute to your handler: 

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Route(pattern="/entity/{entity_id:[1-9][0-9]*}")
 * @PHA\Attribute(name="IdentifiableValueLoad", options={"id_attr":"entity_id","type":"entity_type","value_attr":"entity"})
 */
class Handler
{
    /**
     * @PHA\Get()
     */
    public function handleGet(ServerRequestInterface $request)
    {
        /** @var EntityClass $entity */
        $entity = $request->getAttribute('entity');//This attribute will store loaded identifiable value
    }
}
```
```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity/{entity_id:[1-9][0-9]*}")]
#[PHA\Attribute("IdentifiableValueLoad", ["id_attr" => "entity_id", "type" => "entity_type", "value_attr" => "entity"])
class Handler
{
    #[PHA\Get()]
    public function handleGet(ServerRequestInterface $request)
    {
        /** @var EntityClass $entity */
        $entity = $request->getAttribute('entity');//This attribute will store loaded identifiable value
    }
}
```

For details see available options: `Articus\PathHandler\Attribute\Options\IdentifiableValueLoad`.


### `Transfer` usage

Set up `Articus\DataTransfer\Service` (check [Data Transfer documentation](https://github.com/Articus/DataTransfer#how-to-use) for details) and then just add attribute to your handler: 

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Route(pattern="/entity")
 */
class Handler
{
    /**
     * @PHA\Post()
     * @PHA\Attribute(name="Transfer", options={"type":DTO::class,"subset":"part","source":"get","objectAttr":"dto","errorAttr":"errors"})
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $errors = $request->getAttribute('errors');//This attribute will store validation errors
        if (empty($errors))
        {
            /** @var DTO $dto */
            $dto = $request->getAttribute('dto');//Valid DTO filled with data from query params
        }
    }
}
```
```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
class Handler
{
    #[PHA\Post()]
    #[PHA\Attribute("Transfer", ["type"=>DTO::class,"subset"=>"part","source"=>"get","objectAttr"=>"dto","errorAttr"=>"errors"])]
    public function handlePost(ServerRequestInterface $request)
    {
        $errors = $request->getAttribute('errors');//This attribute will store validation errors
        if (empty($errors))
        {
            /** @var DTO $dto */
            $dto = $request->getAttribute('dto');//Valid DTO filled with data from query params
        }
    }
}
```

For details see available options: `Articus\PathHandler\Attribute\Options\Transfer`.
