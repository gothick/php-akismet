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

	/**
	 * Make a result, throwing exceptions on invalid responses and non-200 http status codes.
	 * 
	 * @param \GuzzleHttp\Psr7\Response $response
	 * @param array $valid_values
	 * @throws Exception
	 */
	public function __construct(\GuzzleHttp\Psr7\Response $response, $valid_values)
	{
		if ($response->hasHeader('X-akismet-debug-help'))
		{
			$this->debug_help = $response->getHeaderLine('X-akismet-debug-help');
		}
		if ($response->hasHeader(self::PRO_TIP_HEADER))
		{
			$this->pro_tip = $response->getHeaderLine(self::PRO_TIP_HEADER);
		}

		$status_code = $response->getStatusCode();
		$this->raw_result = (string) $response->getBody();

		if ($status_code != 200 || !in_array($this->raw_result, $valid_values))
		{
			// Our clients are meant to check first
			$message = 'Invalid ' . $status_code . ' response :' . $this->raw_result . ' in ' . __METHOD__;
			if ($this->hasDebugHelp())
			{
				$message .= ' (debug help: ' . $this->getDebugHelp() . ')';
			}
			throw new Exception($message);
		}
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
