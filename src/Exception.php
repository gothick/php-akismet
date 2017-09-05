<?php 
namespace Gothick\AkismetClient;

class Exception extends \Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        throw new \Exception('Gothick\AkismetClient: ' . $message);
    }
}