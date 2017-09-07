<?php
namespace Gothick\AkismetClient;

class VerifyKeyResult extends ClientResult
{
	public function __construct(\GuzzleHttp\Psr7\Response $response)
	{
		parent::__construct($response, [ 'valid', 'invalid' ]);
	}

	public function isValid() {
		return $this->raw_result == 'valid';
	}
}