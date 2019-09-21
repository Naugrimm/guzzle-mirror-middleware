<?php

declare(strict_types=1);
namespace GuzzleMirror;
use Closure;
use GuzzleMirror\Exceptions\MirrorFailedException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Promise\each_limit;
use function in_array;

/**
 * Guzzle Mirror Middleware
 *
 * Guzzle 6 middleware that mirrors requests.
 *
 * @author Hasnat Ullah <hasnat.ullah@gmail.com>
 */
class GuzzleMirrorMiddleware
{
    /**
     * @var array
     */
    private $options = [
        'mirrors'                  => [],
        'default_mirror_options'    => [
            'ignore_mirror_failures'    => false,
            'ignore_mirror_methods'     => []
        ],
        'mirrors_concurrency'       => 4,
        'no_mirror_on_failure'      => true,
        'mirror_responses'          => null,
        'ignored_failures_callback' => null
    ];

    /**
     * GuzzleMirrorMiddleware constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = []) {
        $this->options = array_replace($this->options, $options);
        foreach ($this->options['mirrors'] as $index => $mirror) {
            $this->options['mirrors'][$index] = array_replace(
                [
                    'ignore_mirror_failures'    => $this->options['ignore_mirror_failures'] ?? $this->options['default_mirror_options']['ignore_mirror_failures'],
                    'ignore_mirror_methods'     => $this->options['ignore_mirror_methods'] ?? $this->options['default_mirror_options']['ignore_mirror_methods'],
                ],
                $mirror
            );
        }

    }

    /**
     * @param callable $handler
     * @return Closure
     */
    public function __invoke(callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)->then(
                $this->sendMirrors($request, $options, $this->options)
            );
        };
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @param array $middlewareOptions
     * @return callable
     */
    protected function sendMirrors(RequestInterface $request, array $options, $middlewareOptions): callable {
        return function (ResponseInterface $response) use ($request, $options, $middlewareOptions) {
            $ignoreFailures = false;
            $promises = function () use ($request, $options, $middlewareOptions, &$ignoreFailures) {
                foreach (array_filter($middlewareOptions['mirrors'], function($mirror) use ($request, $middlewareOptions) {
                    if (in_array(
                        $request->getMethod(),
                        $mirror['ignore_mirror_methods']
                    )) {
                        return false;
                    }
                    return true;
                }) as $mirror) {
                    /** @var \GuzzleHttp\Client $client */
                    $ignoreFailures = $mirror['ignore_mirror_failures'] || $ignoreFailures;
                    $client = $mirror['client'];
                    yield $client->sendAsync($request);
                };
            };
            $failures = [];
            $mirrorResponses = [];
            each_limit(
                $promises(),
                $middlewareOptions['mirrors_concurrency'],
                function (ResponseInterface $response, $idx) use (&$mirrorResponses) {
                    $mirrorResponses[$idx] = $response;
                },
                function ($reason, $idx) use (&$failures) {
                    $failures[$idx] = $reason;
                }
            )->wait();
            if ($middlewareOptions['mirror_responses'] != null) {
                $middlewareOptions['mirror_responses']($mirrorResponses);
            }
            if (count($failures) && !$ignoreFailures) {
                throw new MirrorFailedException($response, $failures);
            } else if (count($failures) && $middlewareOptions['ignored_failures_callback'] != null) {
                $middlewareOptions['ignored_failures_callback']($failures);
            }

            return $response;
        };
    }

}