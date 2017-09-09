<?php
namespace Gothick\AkismetClient;

/**
 * Result of calling the submit-ham method on the Akismet API.
 * @author matt
 *
 */
class SubmitHamResult extends ClientResult
{
	/**
	 * Create a result; throws if the result body isn't in the known list of responses.
	 * @param \GuzzleHttp\Psr7\Response $response
	 */
	public function __construct(\GuzzleHttp\Psr7\Response $response)
	{
		parent::__construct($response, [ 'Thanks for making the web a better place.' ]);
	}
}
