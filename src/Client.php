<?php
namespace Gothick\AkismetClient;

/**
 * Akismet API client.
 * @author matt
 *
 */
class Client
{
	const VERB_VERIFY_KEY = 'verify-key';
	const VERB_COMMENT_CHECK = 'comment-check';
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

	/**
	 * Headers to be sent on every API call
	 * 
	 * @return string[]
	 */
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

	/**
	 * From the docs:
	 * Setting your user agent If possible, your user agent string should always use the following format: Application Name/Version | Plugin Name/Version
	 * e.g. WordPress/4.4.1 | Akismet/3.1.7
	 * 
	 * @return string
	 */
	private function getOurUserAgent()
	{
		return "{$this->app_name}/{$this->app_version} | Gothick\\AkismetClient/" . self::VERSION;
	}

	/**
	 * You may want to verify a key before you use it. To do that, construct a Client without an API
	 * key, then use verifyKey($key) to verify the key, then use setKey($key) to set the validated
	 * key. You can call verifyKey without a key set, but you must set a key before calling any other
	 * API method.
	 * 
	 * @param string $api_key
	 * @throws Exception
	 */
	public function setKey($api_key)
	{
		if (empty($api_key))
		{
			throw new Exception('Must provide an API key in ' . __METHOD__);
		}
		$this->api_key = $api_key;
	}

	/**
	 * Verify an Akismet API key.
	 * @param string $api_key
	 * @throws Exception
	 * @return \Gothick\AkismetClient\VerifyKeyResult
	 */
	public function verifyKey($api_key = null)
	{
		$key_to_verify = empty($api_key) ? $this->api_key : $api_key;

		if (empty($key_to_verify))
		{
			throw new Exception('Must provide or pre-configure a key in ' . __METHOD__);
		}

		try
		{
			$params = [
					"key" => $key_to_verify,
					"blog" => $this->blog 
			];
			$response = $this->callApiMethod(self::VERB_VERIFY_KEY, $params);
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
	public function commentCheck($params = array(), $server_params = array())
	{
		// According to the Akismet docs, these two (and 'blog', which we have as $this->blog already) are
		// the only required parameters. Seems odd, but hey.
		if (empty($params[ 'user_ip' ]) || empty($params[ 'user_agent' ]))
		{
			throw new Exception(__METHOD__ . ' requires user_ip and user_agent in $params');
		}

		$params = array_merge($server_params, $params);
		$params = array_merge($params, [
				'blog' => $this->blog
		]);

		try
		{
			$response = $this->callApiMethod(self::VERB_COMMENT_CHECK, $params);
		} catch (\Exception $e)
		{
			throw new Exception('Unexpected exception in ' . __METHOD__, 0, $e);
		}
		return new CommentCheckResult($response);
	}

	/**
	 * Call an Akisemet API method.
	 * @param string $verb
	 * @param array $params
	 * @return \GuzzleHttp\Psr7\Response
	 */
	private function callApiMethod($verb, $params)
	{
		return $this->guzzle_client->request(
				'POST',
				$this->apiUri($verb),
				[
						'form_params' => $params,
						'headers' => $this->getStandardHeaders()
				]);
	}

	/**
	 * Work out the Akismet API URL given the REST verb and our configured key. This would
	 * be far less of a pain if Akismet just had you pass the API key as a parameter or 
	 * a header. Gawd knows why they change the host for authenticated calls.
	 * @param string $verb
	 * @throws Exception
	 * @return string
	 */
	private function apiUri($verb)
	{
		if ($verb == self::VERB_VERIFY_KEY)
		{
			return "https://rest.akismet.com/1.1/verify-key";
		} else
		{
			if (empty($this->api_key))
			{
				throw new Exception("Can't call authenticated method without setting an API key in " . __METHOD__);
			}
			return "https://{$this->api_key}.rest.akismet.com/1.1/$verb";
		}
	}
}