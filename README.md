Guzzle Mirror Middleware
=====

[![Latest Version](https://img.shields.io/github/release/hasnat/guzzle-mirror-middleware.svg?style=flat-square)](https://github.com/hasnat/guzzle-mirror-middleware/releases)
[![Build Status](https://img.shields.io/travis/hasnat/guzzle-mirror-middleware/master.svg?style=flat-square)](https://travis-ci.org/hasnat/guzzle-mirror-middleware)
[![Total Downloads](https://img.shields.io/packagist/dt/hasnat/guzzle-mirror-middleware.svg?style=flat-square)](https://packagist.org/packages/hasnat/guzzle-mirror-middleware)

This package provides middleware for [guzzle](https://github.com/guzzle/guzzle/) for mirroring request to multiple clients:

Installation
-------

To install, use composer:

```
composer require hasnat/guzzle-mirror-middleware
```

Usage
-------

To use this middleware, you need to initialize it like:

Setup Mirror Clients:
```php
$mirrorsMiddleware = new \GuzzleMirror\GuzzleMirrorMiddleware([
    'mirrors' => [
        ['client' => new \GuzzleHttp\Client(['base_uri' => 'http://mirror1.com/'])],
        ['client' => new \GuzzleHttp\Client(['base_uri' => 'http://mirror2.com/'])],
        ['client' => new \GuzzleHttp\Client(['base_uri' => 'http://mirror3.com/'])]
    ]
])
```

And inject it to Guzzle with something like:
```php
$handlerStack = HandlerStack::create();
$handlerStack->push(new \GuzzleMirror\GuzzleMirrorMiddleware([
    'mirrors' => [
        ['client' => new \GuzzleHttp\Client(['base_uri' => 'http://mirror1.com/'])],
        ['client' => new \GuzzleHttp\Client(['base_uri' => 'http://mirror2.com/'])],
        ['client' => new \GuzzleHttp\Client(['base_uri' => 'http://mirror3.com/'])]
    ]
]));
$this->client = new GuzzleHttp\Client([
    'base_uri' => 'base_uri' => 'http://example.com/',
    'handler' => $handlerStack
]);
```

From now on every request sent with `$guzzleClient` will be replicated on mirrors.


Testing
-------

`hasnat/guzzle-mirror-middleware` has a [PHPUnit](https://phpunit.de) test suite and a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/).

To run the tests, run the following command from the project folder.

``` bash
$ docker-compose run test
```


License
-------

MIT

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
