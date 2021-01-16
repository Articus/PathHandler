# Path Handler

[![GitHub Actions: Run tests](https://github.com/Articus/PathHandler/workflows/Run%20tests/badge.svg)](https://github.com/Articus/PathHandler/actions?query=workflow%3A%22Run+tests%22)
[![Documentation](https://readthedocs.org/projects/pathhandler/badge/?version=latest)](http://pathhandler.readthedocs.io/en/latest/?badge=latest)
[![Coveralls](https://coveralls.io/repos/github/Articus/PathHandler/badge.svg?branch=master)](https://coveralls.io/github/Articus/PathHandler?branch=master)
[![Codacy](https://api.codacy.com/project/badge/Grade/02dc4cfb69e34079ab380593fe5f4f70)](https://www.codacy.com/app/articusw/PathHandler?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Articus/PathHandler&amp;utm_campaign=Badge_Grade)


This library considerably simplifies API development with [Mezzio](https://docs.mezzio.dev/mezzio/) by reducing amount of boilerplate code you have to write for each API operation. The idea is to provide a more convenient way to deal with:

- routing - routes for all operations are registered automatically
- consuming - each operation may have unique algorithm to parse request body according its content type
- attributing (as in [PSR-7 request attributes](https://www.php-fig.org/psr/psr-7/#15-server-side-requests)) - each operation may have its own set of request attributes calculated from raw request data (like current user information insteadof authentication header, validated DTO insteadof form value array, entity object insteadof query parameter with its id and so on)
- producing - each operation may have unique algorithm to prepare response body from operation result according media type accepted by client

So you can focus on handling your API operations and spend less time on writing auxiliary code for request processing.

## How to install?

Just add `"articus/path-handler"` to your [composer.json](https://getcomposer.org/doc/04-schema.md#require) and check [packages suggested by the library](https://getcomposer.org/doc/04-schema.md#suggest) for extra dependencies of optional components you want to use.  

> *Note* - library has [Laminas Service Manager](https://docs.laminas.dev/laminas-servicemanager/) as direct dependency but only because of [plugin managers](https://docs.laminas.dev/laminas-servicemanager/plugin-managers/). So you can use this library with any PSR-11 container you like.  

## How to use?

First of all you need a project with Mezzio application. For example, you can generate one with [this installer](https://github.com/mezzio/mezzio-skeleton).  

Next you need to declare **handlers**. Each handler is a set of all **operations** that can be performed when some **path** of your API is accessed with distinct HTTP methods. Any class can be a handler, you just need to decorate it with special annotations:

```PHP
namespace My;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This is how you set path for handler operations
 * @PHA\Route(pattern="/entity")
 */
class Handler
{
    /**
     * This is how you declare HTTP method of the operation
     * @PHA\Post()
     * This is how you consume request body
     * @PHA\Consumer(name="Json", mediaType="application/json")
     * This is how you attribute request
     * @PHA\Attribute(name="Transfer", options={"type":"My\DTO","objectAttr":"dto","errorAttr":"errors"})
     * This is how you produce response body from returned value
     * @PHA\Producer(name="Json", mediaType="application/json")
     */
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

Finally you need to configure special factory for router service. Here is a sample configuration for [Laminas Service Manager](https://docs.laminas.dev/laminas-servicemanager/) (example is in YAML just for readability):

```YAML
dependencies:
  factories:
    Mezzio\Router\RouterInterface: Articus\PathHandler\RouteInjection\Factory

Articus\PathHandler\RouteInjection\Factory:
  paths:
    '':
    # List of your handlers   
    - My\Handler
  # Configuration for handler plugin manager - sub-container dedicated for handlers
  handlers:
    factories:
      My\Handler: My\HandlerFactory
```

For more details check [documentation](http://pathhandler.readthedocs.io/en/latest/).

# Enjoy!
I hope this library will be useful for someone except me. 
It is used for production purposes but it lacks lots of refinement, especially in terms of tests and documentation. 

If you have any suggestions, advices, questions or fixes feel free to submit issue or pull request.
