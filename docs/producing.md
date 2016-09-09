### Producing

To produce response body you need a **producer** - class that implements `Articus\PathHandler\Producer\ProducerInterface` and is registered in configuration:
 
```YAML
path_handler:
  #Add entry in producer plugin manager 
  producers:
    invokables:
      MyProducer: My\Producer 
```

Library provides three producers out of the box:

- `Json` to encode operation result as JSON string
- `Transfer` - extension of `Json` producer that uses [Data Transfer library](https://github.com/Articus/DataTransfer) to extract data from operation result before encoding
- `Template` that gets template name and data from operation result and uses `Zend\Expressive\Template\TemplateRendererInterface` to render it 

To use producer for operation in your handler you just need to annotate operation method:

```PHP
namespace My;

use Articus\PathHandler\Operation\GetInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

class Handler implements GetInterface
{
    /**
     * @PHA\Producer(name="Json", mediaType="application/json")
     */
    public function handleGet(ServerRequestInterface $request)
    {
        return ['some' => 'thing']; 
    }
}
```

Setting `mediaType` is used for both matching against request `Accept` header and as a value for response `Content-Type` header. 
Specify several producers if you want to allow client to choose how content will be encoded:

```PHP
namespace My;

use Articus\PathHandler\Operation\GetInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

class Handler implements GetInterface
{
    /**
     * @PHA\Producer(name="Json", mediaType="application/json")
     * @PHA\Producer(name="Template", mediaType="text/html")
     */
    public function handleGet(ServerRequestInterface $request)
    {
        return ['success', ['some' => 'thing']]; 
    }
}
```

If all operations in your handler need same producer you can just annotate handler class insteadof annotating each method:

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Operation\PatchInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Producer(name="Json", mediaType="application/json")
 */
class Handler implements PostInterface, PatchInterface
{
    public function handlePost(ServerRequestInterface $request)
    {
        return ['some' => 'thing']; 
    }
    public function handlePatch(ServerRequestInterface $request)
    {
        return ['some' => 'thing']; 
    }
}
