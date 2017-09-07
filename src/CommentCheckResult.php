<?php
namespace Gothick\AkismetClient;

class CommentCheckResult extends ClientResult
{
	public function __construct(\GuzzleHttp\Psr7\Response $response)
	{
		parent::__construct($response, ['true', 'false']);
	}

	public function isSpam() {
		return $this->raw_result == 'true';
	}
	public function isBlatantSpam() {
		return ($this->isSpam() && $this->pro_tip == 'disacrd');
	}
}