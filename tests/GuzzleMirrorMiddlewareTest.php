<?php

namespace GuzzleMirror;

use GuzzleHttp\Middleware;
use GuzzleMirror\Exceptions\MirrorFailedException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class GuzzleMirrorMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function sendRequestsToAllMirrors()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
        ]);
        $mirrorsStack = HandlerStack::create($mock);
        $mirrorsStack->push($history);
        $stack = HandlerStack::create($mock);
        $mirror = new GuzzleMirrorMiddleware([
            'mirrors' => [
                ['client' => new Client(['base_uri' => 'http://mirror1.com/', 'handler' => $mirrorsStack])],
                ['client' => new Client(['base_uri' => 'http://mirror2.com/', 'handler' => $mirrorsStack])],
                ['client' => new Client(['base_uri' => 'http://mirror3.com/', 'handler' => $mirrorsStack])]
            ],
        ]);

        $stack->push($history);
        $stack->push($mirror);
        $client = new Client(['handler' => $stack]);

        $client->request('GET', 'http://example.com/');
        $this->assertEquals(4, count($container));
    }

    /**
     * @test
     */
    public function throwsExceptionOnMirrorFailure()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, ['X-Mirror' => false]),
            new Response(500, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
        ]);
        $mirrorsStack = HandlerStack::create($mock);
        $mirrorsStack->push($history);
        $stack = HandlerStack::create($mock);
        $mirror = new GuzzleMirrorMiddleware([
            'mirrors' => [
                ['client' => new Client(['base_uri' => 'http://mirror1.com/', 'handler' => $mirrorsStack])],
                ['client' => new Client(['base_uri' => 'http://mirror2.com/', 'handler' => $mirrorsStack])],
                ['client' => new Client(['base_uri' => 'http://mirror3.com/', 'handler' => $mirrorsStack])]
            ],
        ]);

        $stack->push($history);
        $stack->push($mirror);
        $client = new Client(['handler' => $stack]);

        $hadException = false;
        try {
            $client->request('GET', 'http://example.com/')->getStatusCode();
        } catch (MirrorFailedException $e) {
            $hadException = true;
        }
        $this->assertTrue($hadException);
    }

    /**
     * @test
     */
    public function ignoreRequestIfInIgnoreMirrorMethod()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
        ]);
        $mirrorsStack = HandlerStack::create($mock);
        $mirrorsStack->push($history);
        $stack = HandlerStack::create($mock);
        $mirror = new GuzzleMirrorMiddleware([
            'mirrors' => [
                ['client' => new Client(['base_uri' => 'http://mirror1.com/', 'handler' => $mirrorsStack])],
                ['ignore_mirror_methods' => ['GET'], 'client' => new Client(['base_uri' => 'http://mirror2.com/', 'handler' => $mirrorsStack])],
                ['client' => new Client(['base_uri' => 'http://mirror3.com/', 'handler' => $mirrorsStack])]
            ],
        ]);

        $stack->push($history);
        $stack->push($mirror);
        $client = new Client(['handler' => $stack]);

        $hadException = false;

        $client->request('GET', 'http://example.com/')->getStatusCode();
        $this->assertEquals(3, count($container));
    }

    /**
     * @test
     */
    public function ignoreAllRequestsIfInIgnoreMirrorMethod()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
            new Response(200, ['X-Mirror' => false]),
        ]);
        $mirrorsStack = HandlerStack::create($mock);
        $mirrorsStack->push($history);
        $stack = HandlerStack::create($mock);
        $mirror = new GuzzleMirrorMiddleware([
            'mirrors' => [
                ['client' => new Client(['base_uri' => 'http://mirror1.com/', 'handler' => $mirrorsStack])],
                ['client' => new Client(['base_uri' => 'http://mirror2.com/', 'handler' => $mirrorsStack])],
                ['client' => new Client(['base_uri' => 'http://mirror3.com/', 'handler' => $mirrorsStack])]
            ],
            'ignore_mirror_methods' => ['GET'],
        ]);

        $stack->push($history);
        $stack->push($mirror);
        $client = new Client(['handler' => $stack]);

        $hadException = false;

        $client->request('GET', 'http://example.com/')->getStatusCode();
        $this->assertEquals(1, count($container));
    }

}
