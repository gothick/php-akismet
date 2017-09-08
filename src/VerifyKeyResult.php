<?php
namespace Gothick\AkismetClient;

/**
 * Result of calling the verify-key method on the Akismet API.
 * @author matt
 *
 */
class VerifyKeyResult extends ClientResult
{
	/**
	 * Create a result; throws if the result body isn't in the known list of responses.
	 * @param \GuzzleHttp\Psr7\Response $response
	 */
	public function __construct(\GuzzleHttp\Psr7\Response $response)
	{
		parent::__construct($response, [ 'valid', 'invalid' ]);
	}

	/**
	 * Is the key valid?
	 * @return boolean
	 */
	public function isValid() {
		return $this->raw_result == 'valid';
	}
}