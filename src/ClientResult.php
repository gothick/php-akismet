<?php
namespace Gothick\AkismetClient;

class ClientResult
{

	const PRO_TIP_HEADER = 'X-akismet-pro-tip';

	/**
	 * Raw string we got back from the Akismet API as an answer
	 * @var string
	 */
	protected $raw_result;

	/**
	 * Akismet's X-akismet-pro-tip header, which sometimes has
	 * useful extra information.
	 * @var unknown
	 */
	protected $pro_tip;

	public function __construct (\GuzzleHttp\Psr7\Response $response)
	{
		if ($response->getStatusCode() != 200)
		{
			// Our clients are meant to check first
			throw new Exception(
					'Response with invalid status code in ' . __METHOD__);
		}
		$this->raw_result = (string) $response->getBody();
		if ($response->hasHeader(self::PRO_TIP_HEADER))
		{
			$this->pro_tip = $response->getHeader(self::PRO_TIP_HEADER);
		}
	}

	public function hasProTip()
	{
		return (!empty($this->pro_tip));
	}
	public function getProTip()
	{
		return $this->pro_tip;
	}
}
