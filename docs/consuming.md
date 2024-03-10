# Consuming

To consume request body you need a **consumer** - class that implements `Articus\PathHandler\Consumer\ConsumerInterface` and is registered in configuration:
 
```YAML
#Add entry in consumer plugin manager 
Articus\PathHandler\Consumer\Factory\PluginManager:
  invokables:
    MyConsumer: My\Consumer 
```

Library provides two consumers out of the box:

- `Internal` for `application/x-www-form-urlencoded` and `multipart/form-data` mime types when all work is already done by PHP SAPI
- `Json` for `application/json` mime type when request body is JSON string

To use consumer for operation in your handler you just need to annotate operation method:

```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
class Handler
{
    #[PHA\Post()]
    #[PHA\Consumer("*/*", "Json")]
    public function handlePost(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody(); 
    }
}
```

Each operation method can have several consumers. Just specify media range to determine when each of them should be called according request content type: 

```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
class Handler
{
    #[PHA\Post()]    
    #[PHA\Consumer("application/json", "Json")]
    #[PHA\Consumer("multipart/form-data", "Internal")]
    public function handlePost(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody(); 
    }
}
```
It is recommended to always specify `mediaRange` to enforce anyone calling your API to supply a valid content type. 
If all operations in your handler need same consumer you can just annotate handler class insteadof annotating each method: 

```PHP
namespace My;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface;

#[PHA\Route("/entity")]
#[PHA\Consumer("application/json", "Json")]
class Handler
{
    #[PHA\Post()]
    public function handlePost(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody(); 
    }
    #[PHA\Patch()]
    public function handlePatch(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody(); 
    }
}
```
