<?php

namespace Dormilich\HttpClient\Exception;

use Dormilich\HttpClient\ExceptionInterface;

use function get_class;
use function gettype;
use function is_object;

class UnsupportedDataTypeException extends \UnexpectedValueException implements ExceptionInterface
{
    /**
     * @var mixed $data
     */
    private $data;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $type = is_object($data) ? get_class($data) : gettype($data);
        $msg = "There was no encoder configured to encode the request payload of type [{$type}].";
        parent::__construct($msg);

        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
