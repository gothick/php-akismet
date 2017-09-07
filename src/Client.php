<?php
namespace Gothick\AkismetClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;

class Client
{

	/**
	 * Akismet API key
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Our Guzzle client.
	 * This can be passed in for DI, or if not we'll create a default one ouselves.
	 *
	 * @var \GuzzleHttp\Client
	 */
	private $guzzle_client;

	/**
	 * URL of the site using us.
	 * Akismet calls this "blog", because WordPress.
	 *
	 * @var string
	 */
	private $blog;

	/**
	 * Name of the site using us.
	 *
	 * @var string
	 */
	private $app_name;

	/**
	 * Version string of the site using us.
	 *
	 * @var string
	 */
	private $app_version;

	/**
	 * Version of this Client.
	 * Akismet likes to know. We should bump this every time
	 * we package a new version.
	 *
	 * @var string
	 */
	const VERSION = '0.1';

	/**
	 * Make an Akismet API client.
	 * Typically you'd provide an API key in $api_key, at which point you can make any call. Without the optional 
	 * $api_key you're limited to calling verifyApiKey. Once you've verified a key you can call setApiKey() 
	 * later and start using the rest of the API.
	 *
	 * @param string $app_url
	 *        	e.g. http://forum.example.com/
	 * @param string $app_name
	 *        	e.g. phpBB
	 * @param string $app_version
	 *        	e.g. 3.2.1
	 * @param string $api_key
	 *        	(optional) Akismet API key
	 * @param
	 *        	\GuzzleHttp\Client (optional) $guzzle_client. You can inject a mock, or a non-Curl-using Guzzle 
	 *        	client here, say. Otherwise we'll just make one.
	 * @throws Exception
	 */
	public function __construct($app_url, $app_name, $app_version, $api_key = null, $guzzle_client = null)
	{
		if ((empty($app_url)) || (empty($app_name)) || (empty($app_version)))
		{
			throw new Exception('Must supply app URL, name and version in ' . __METHOD__);
		}
		// The Akismet API calls it a blog, so keep consistent.
		$this->blog = $app_url;

		$this->app_name = $app_name;
		$this->app_version = $app_version;
		$this->api_key = $api_key;

		// Our client is passed in, as dependency injection is helpful for 
		// testing, but in the normal course of things we'll probably just
		// create it ourselves.
		$this->guzzle_client = $guzzle_client;
		if (!isset($this->guzzle_client))
		{
			$this->guzzle_client = new \GuzzleHttp\Client();
		}
	}

	private function getStandardHeaders()
	{
		// I'd use Guzzle middleware for this, as we want to add it on 
		// every request, but how do I do that and support dependency 
		// injection of our client? You can't add middleware to a 
		// Guzzle client after it's been constructed, right?
		return array(
				'User-Agent' => $this->getOurUserAgent()
		);
	}

	private function getOurUserAgent()
	{
		// From the docs:
		// Setting your user agent If possible, your user agent string should always use the following format: Application Name/Version | Plugin Name/Version
		// e.g. WordPress/4.4.1 | Akismet/3.1.7
		// TODO: Check this is formatting correctly.
		// TODO: Add unit test
		return "{$this->app_name}/{$this->app_version} | Gothick\\AkismetClient/" . self::VERSION;
	}

	public function setApiKey($api_key)
	{
		if (empty($api_key))
		{
			throw new Exception('Must provide an API key in ' . __METHOD__);
		}
		$this->api_key = $api_key;
	}

	public function verifyKey($api_key = null)
	{
		$key_to_verify = empty($api_key) ? $this->api_key : $api_key;

		if (empty($key_to_verify))
		{
			throw new Exception('Must provide or pre-configure a key in ' . __METHOD__);
		}

		try
		{
			$response = $this->guzzle_client->request('POST', $this->apiUri('verify-key'),
					[
							'form_params' => [
									"key" => $key_to_verify,
									"blog" => $this->blog
							],
							'headers' => $this->getStandardHeaders()
					]);

		} catch (\Exception $e)
		{
			// Wrap whatever exception we caught up in a new exception of our 
			// own type and throw it along up the line.
			throw new Exception('Unexpected exception in ' . __METHOD__, 0, $e);
		}
		return new VerifyKeyResult($response);
	}

	/**
	 * Check a comment for spam.
	 * See the Akismet API documentation for full details:
	 * https://akismet.com/development/api/#comment-check. 
	 * Returns a valid ClientResult object or throws an exception.
	 *
	 * @param array $params
	 *        	User IP, User-Agent, the message, etc. See the Akismet API
	 *        	documentation for details.
	 * @param array $server_params
	 *        	This can just be $_SERVER, if you have access to it
	 * @param string $user_role
	 *        	If 'administrator', will always pass the check
	.*
	 */
	public function commentCheck($params = array(), $server_params = array(), $user_role = 'guest')
	{
		// According to the Akismet docs, these two (and 'blog', which we have as $this->blog already) are
		// the only required parameters. Seems odd, but hey.
		if (empty($params[ 'user_ip' ]) || empty($params[ 'user_agent' ]))
		{
			throw new \InvalidArgumentException(__METHOD__ . ' requires user_ip and user_agent in $params');
		}

		$params = array_merge($server_params, $params);
		$params = array_merge($params, [
				'blog' => $this->blog,
				'user_role' => $user_role
		]);

		try
		{
			$response = $this->guzzle_client->request('POST', $this->apiUri('comment-check'),
					[
							'form_params' => $params,
							'headers' => $this->getStandardHeaders()
					]);
		} catch (\Exception $e)
		{
			throw new Exception('Unexpected exception in ' . __METHOD__, 0, $e);
		}
		return new CommentCheckResult($response);
	}

	private function apiUri($method)
	{
		if ($method == 'verify-key')
		{
			return "https://rest.akismet.com/1.1/verify-key";
		} else
		{
			if (empty($this->api_key))
			{
				throw new Exception("Can't call authenticated method without setting an API key in " . __METHOD__);
			}
			return "https://{$this->api_key}.rest.akismet.com/1.1/$method";
		}
	}
}