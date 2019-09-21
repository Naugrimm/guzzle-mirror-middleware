<?php

namespace GuzzleMirror\Exceptions;

class MirrorFailedException extends \Exception
{
    /**
     * @var array
     */
    public $reasons;
    public $mainResponse;

    /**
     * MirrorFailed constructor.
     * @param $response
     * @param array $reasons
     */
    public function __construct($response, $reasons = [])
    {
        $this->mainResponse = $response;
        $this->reasons = $reasons;
        parent::__construct(print_r(array_map(
            function($reason) {return $reason->getMessage();},
            $reasons
        )), true);
    }

    /**
     * @return array
     */
    public function getReasons(): array
    {
        return $this->reasons;
    }

    /**
     * @return mixed
     */
    public function getMainResponse()
    {
        return $this->mainResponse;
    }

}