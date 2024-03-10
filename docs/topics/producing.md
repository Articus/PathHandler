# Producing

To produce response body you need a **producer** - class that implements `Articus\PathHandler\Producer\ProducerInterface` and is registered in configuration:
 
```YAML
#Add entry in producer plugin manager 
Articus\PathHandler\Producer\PluginManager:
  invokables:
    MyProducer: My\Producer 
```

Library provides four producers out of the box:

- `Text` for strings and stringable objects
- `Json` to encode operation result as JSON string
- `Transfer` that uses [Data Transfer library](https://github.com/Articus/DataTransfer) to extract data from operation result and passes this data to another producer (`Json` by default)
- `Template` that gets template name and data from operation result and uses `Mezzio\Template\TemplateRendererInterface` to render it 

To use producer for operation in your handler you just need to annotate operation method:

```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
class Handler
{
    #[PHA\Get()]
    #[PHA\Producer("application/json", "Json")]
    public function handleGet(ServerRequestInterface $request): array
    {
        return ['some' => 'thing']; 
    }
}
```

Setting `mediaType` is used for both matching against request `Accept` header and as a value for response `Content-Type` header. 
Specify several producers if you want to allow client to choose how content will be encoded:

```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
class Handler
{
    #[PHA\Get()]
    #[PHA\Producer("application/json", "Json")]
    #[PHA\Producer("text/html", "Template")]
    public function handleGet(ServerRequestInterface $request): array
    {
        return ['success', ['some' => 'thing']]; 
    }
}
```

If all operations in your handler need same producer you can just annotate handler class insteadof annotating each method:

```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
#[PHA\Producer("application/json", "Json")]
class Handler
{
    #[PHA\Post()]
    public function handlePost(ServerRequestInterface $request): array
    {
        return ['some' => 'thing']; 
    }
    #[PHA\Patch()]
    public function handlePatch(ServerRequestInterface $request): array
    {
        return ['some' => 'thing']; 
    }
}
```

For requests with mangled or unsupported `Accept` header library will generate `text/plain` response using `Text` producer. You may specify another producer for such situations in configuration:

```YAML
Articus\PathHandler\RouteInjectionFactory:
  # Same arguments as in producer declaration for handlers  
  default_producer:
    media_type: custom-media-type/for-bad-requests
    name: MyProducerForBadRequests
    options:
      some: value
```
