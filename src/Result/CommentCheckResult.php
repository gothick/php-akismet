<?php
namespace Gothick\AkismetClient\Result;

/**
 * Result of calling the client-check method on the Akismet API.
 * @author matt
 *
 */
class CommentCheckResult extends ClientResult
{
	/**
	 * Create a result; throws if the result body isn't in the known list of responses.
	 * @param \GuzzleHttp\Psr7\Response $response
	 */
	public function __construct(\GuzzleHttp\Psr7\Response $response)
	{
		parent::__construct($response, [ 'true', 'false' ]);
	}

	/**
	 * Does Akismet think the comment is spam?
	 * @return boolean
	 */
	public function isSpam() {
		return $this->raw_result == 'true';
	}
	/**
	 * Is the comment blatant spam which can definitely be discarded without
	 * human intervention? See https://blog.akismet.com/2014/04/23/theres-a-ninja-in-your-akismet/
	 *
	 * @return boolean
	 */
	public function isBlatantSpam() {
		return ($this->isSpam() && $this->hasProTip() && $this->getProTip() == 'discard');
	}
}