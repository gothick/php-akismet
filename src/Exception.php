<?php
namespace Gothick\AkismetClient;

class Exception extends \Exception
{

	public function __construct ($message, $code = 0, \Exception $previous = null)
	{
		parent::__construct('Gothick\AkismetClient: ' . $message, $code,
				$previous);
	}
}