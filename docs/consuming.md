# Consuming

To consume request body you need a **consumer** - class that implements `Articus\PathHandler\Consumer\ConsumerInterface` and is registered in configuration:
 
```YAML
path_handler:
  #Add entry in consumer plugin manager 
  consumers:
    invokables:
      MyConsumer: My\Consumer 
```

Library provides two consumers out of the box:

- `Internal` for `application/x-www-form-urlencoded` and `multipart/form-data` mime types when all work is already done by PHP SAPI
- `Json` for `application/json` mime type when request body is JSON string

To use consumer for operation in your handler you just need to annotate operation method:

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

class Handler implements PostInterface
{
    /**
     * @PHA\Consumer(name="Json")
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody(); 
    }
}
```

Each operation method can have several consumers. Just specify request content type to determine when each of them should be called: 

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

class Handler implements PostInterface
{
    /**
     * @PHA\Consumer(name="Json", mediaType="application/json")
     * @PHA\Consumer(name="Internal", mediaType="multipart/form-data")
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody(); 
    }
}
```
It is recommended to always specify mediaType to enforce anyone calling your API to supply a valid content type. 
If all operations in your handler need same consumer you can just annotate handler class insteadof annotating each method: 

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Operation\PatchInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Consumer(name="Json", mediaType="application/json")
 */
class Handler implements PostInterface, PatchInterface
{
    public function handlePost(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody(); 
    }
    public function handlePatch(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody(); 
    }
}
```
