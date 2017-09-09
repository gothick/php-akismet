<?php
namespace Gothick\AkismetClient\Result;

/**
 * Base class for Akismet client results, the other *Result classes.
 * @author matt
 *
 */
abstract class ClientResult
{

	const PRO_TIP_HEADER = 'X-akismet-pro-tip';
	const DEBUG_HELP_HEADER = 'X-akismet-debug-help';
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
		if ($response->hasHeader(self::DEBUG_HELP_HEADER))
		{
			$this->debug_help = $response->getHeaderLine(self::DEBUG_HELP_HEADER);
		}
		if ($response->hasHeader(self::PRO_TIP_HEADER))
		{
			$this->pro_tip = $response->getHeaderLine(self::PRO_TIP_HEADER);
		}

		$status_code = $response->getStatusCode();
		$this->raw_result = (string) $response->getBody();

		if ($status_code != 200 || !in_array($this->raw_result, $valid_values))
		{
			$message = 'Invalid ' . $status_code . ' response :' . $this->raw_result . ' in ' . __METHOD__;
			if ($this->hasDebugHelp())
			{
				$message .= ' (debug help: ' . $this->getDebugHelp() . ')';
			}
			throw new \Gothick\AkismetClient\AkismetException($message);
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
		return $this->debug_help;
	}
}
