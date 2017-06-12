# Attributing

To attribute request you need an **attribute** - class that implements `Articus\PathHandler\Attribute\AttributeInterface`:

```PHP
namespace My;

use Articus\PathHandler\Attribute\AttributeInterface;
use Psr\Http\Message\ServerRequestInterface;

class Attribute implements AttributeInterface
{
    public function __invoke(ServerRequestInterface $request)
    {
        return $request->withAttribute('some', 'thing'); 
    }
}
```
 
... and is registered in configuration:
 
```YAML
path_handler:
  #Add entry in attribute plugin manager 
  attributes:
    invokables:
      MyAttribute: My\Attribute 
```

To use attribute for operation in your handler you just need to annotate operation method:

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

class Handler implements PostInterface
{
    /**
     * @PHA\Attribute(name="MyAttribute")
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some'); 
    }
}
```

It is possible to pass configuration options to your attribute factory:

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

class Handler implements PostInterface
{
    /**
     * @PHA\Attribute(name="MyAttribute", options={"key":"value"})
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $value = $request->getAttribute('some'); 
    }
}
```

If you set multiple attributes for operation they will be invoked in the same order they appear in annotations:

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @PHA\Attribute(name="First")
 * @PHA\Attribute(name="Second")
 */
class Handler implements PostInterface
{
    /**
     * @PHA\Attribute(name="Third")
     * @PHA\Attribute(name="Fourth")
     */
    public function handlePost(ServerRequestInterface $request)
    {
    }
}
```

Or you can adjust this order with priority setting (default value is 1). Attributes with higher priority will be executed earlier:

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

class Handler implements PostInterface
{
    /**
     * @PHA\Attribute(name="Second")
     * @PHA\Attribute(name="First", priority=10)
     */
    public function handlePost(ServerRequestInterface $request)
    {
    }
}
```

Library provides just one attribute out of the box - `Transfer` that uses [Data Transfer library](https://github.com/Articus/DataTransfer) to construct DTO and fill it data from request only if this data is valid.

```PHP
namespace My;

use Articus\PathHandler\Operation\PostInterface;
use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface;

class Handler implements PostInterface
{
    /**
     * @PHA\Attribute(name="Transfer", options={"type":DTO::class,"source":"get","objectAttr":"dto","errorAttr":"errors"})
     */
    public function handlePost(ServerRequestInterface $request)
    {
        $errors = $request->getAttribute('errors');//This attribute will store validation errors
        if (empty($errors))
        {
            /** @var DTO $dto */
            $dto = $this->getAttribute('dto');//Valid DTO filled with data from query params
        }
    }
}
```

For details see available options: `Articus\PathHandler\Attribute\Options\Transfer`.
