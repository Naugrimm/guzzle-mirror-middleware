<?php

namespace GuzzleMirror\Exceptions;

use Psr\Http\Message\ResponseInterface;

class MirrorFailedException extends \Exception
{
    public array $reasons;
    public ResponseInterface $mainResponse;

    /**
     * MirrorFailed constructor.
     *
     * @param ResponseInterface $response The original response
     * @param array             $reasons  The reasons why the mirrored
     *                                    requests failed
     */
    public function __construct(ResponseInterface $response, array $reasons = [])
    {
        $this->mainResponse = $response;
        $this->reasons = $reasons;
        parent::__construct(
            print_r(
                array_map(
                    function ($reason) {
                        return $reason->getMessage();
                    },
                    $reasons
                )
            ),
            true
        );
    }

    /**
     * Get the reasons why the mirrored requests failed
     *
     * @return array
     */
    public function getReasons(): array
    {
        return $this->reasons;
    }

    /**
     * Get the original response
     *
     * @return ResponseInterface
     */
    public function getMainResponse() : ResponseInterface
    {
        return $this->mainResponse;
    }
}
