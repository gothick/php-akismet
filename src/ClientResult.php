<?php
namespace Gothick\AkismetClient;

abstract class ClientResult
{

	const PRO_TIP_HEADER = 'X-akismet-pro-tip';

	/**
	 * @var string Raw string we got back from the Akismet API as an answer
	 */
	protected $raw_result = '';

	/**
	 * @var string Akismet's X-akismet-pro-tip header, which sometimes has useful extra information.
	 */
	protected $pro_tip = '';

	/**
	 * @var string If there was an X-akismet-debug-header, this is what it contained.
	 */
	protected $debug_help;

	public function __construct(\GuzzleHttp\Psr7\Response $response)
	{
		if ($response->hasHeader('X-akismet-debug-help'))
		{
			$this->debug_help = $response->getHeaderLine('X-akismet-debug-help');
		}
		if ($response->hasHeader(self::PRO_TIP_HEADER))
		{
			$this->pro_tip = $response->getHeaderLine(self::PRO_TIP_HEADER);
		}

		if ($response->getStatusCode() != 200)
		{
			// Our clients are meant to check first
			$message = 'Response with invalid status code ' . $response->getStatusCode() . ' in ' . __METHOD__;
			if ($this->hasDebugHelp())
			{
				$message .= ' (debug help: ' . $this->getDebugHelp() . ')';
			}
			throw new Exception($message);
		}
		$this->raw_result = (string) $response->getBody();
	}

	public function hasProTip()
	{
		return !empty($this->pro_tip);
	}
	public function getProTip()
	{
		return $this->pro_tip;
	}
	public function hasDebugHelp()
	{
		return !empty($this->debug_help);
	}
	public function getDebugHelp()
	{
		return $this->pro_tip;
	}
}
