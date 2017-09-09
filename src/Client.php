<?php
namespace Gothick\AkismetClient;

use \Gothick\AkismetClient\Result\VerifyKeyResult;
use \Gothick\AkismetClient\Result\CommentCheckResult;
use \Gothick\AkismetClient\Result\SubmitHamResult;
use \Gothick\AkismetClient\Result\SubmitSpamResult;

/**
 * Akismet API client.
 * @author matt
 *
 */
class Client
{
	const VERB_VERIFY_KEY = 'verify-key';
	const VERB_COMMENT_CHECK = 'comment-check';
	const VERB_SUBMIT_SPAM = 'submit-spam';
	const VERB_SUBMIT_HAM = 'submit-ham';
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
	 * @throws \Gothick\AkismetClient\AkismetException
	 */
	public function __construct($app_url, $app_name, $app_version, $api_key = null, $guzzle_client = null)
	{
		if ((empty($app_url)) || (empty($app_name)) || (empty($app_version)))
		{
			throw new AkismetException('Must supply app URL, name and version in ' . __METHOD__);
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
	 * @throws \Gothick\AkismetClient\AkismetException
	 */
	public function setKey($api_key)
	{
		if (empty($api_key))
		{
			throw new AkismetException('Must provide an API key in ' . __METHOD__);
		}
		$this->api_key = $api_key;
	}

	/**
	 * Verify an Akismet API key.
	 * @param string $api_key
	 * @param string[] $params Optional parameters. In verify-key, the only useful parameter is "is_test",
	 *                         which you should only pass when testing. To be honest, it's not even clear
	 *                         from the documentation if that parameter is used in verify-key, but better
	 *                         safe than sorry...
	 * @throws \Gothick\AkismetClient\AkismetException
	 * @return \Gothick\AkismetClient\Result\VerifyKeyResult
	 */
	public function verifyKey($api_key = null, $params = array())
	{
		$key_to_verify = empty($api_key) ? $this->api_key : $api_key;

		if (empty($key_to_verify))
		{
			throw new AkismetException('Must provide or pre-configure a key in ' . __METHOD__);
		}

		try
		{
			$params = array_merge(
					$params,
					[
						"key" => $key_to_verify,
						"blog" => $this->blog
					]
			);
			$response = $this->callApiMethod(self::VERB_VERIFY_KEY, $params);
		} catch (\Exception $e)
		{
			// Wrap whatever exception we caught up in a new exception of our
			// own type and throw it along up the line.
			throw new AkismetException('Unexpected exception in ' . __METHOD__, 0, $e);
		}
		return new VerifyKeyResult($response);
	}

	/**
	 * Check a comment for spam.
	 * See the Akismet API documentation for full details:
	 * https://akismet.com/development/api/#comment-check.
	 * Returns a valid CommentCheckResult object or throws an exception.
	 *
	 * @param string[] $params
	 *        	User IP, User-Agent, the message, etc. See the Akismet API
	 *        	documentation for details.
	 * @param string[] $server_params
	 *        	This can just be $_SERVER, if you have access to it
	 * @return \Gothick\AkismetClient\Result\CommentCheckResult
	 */
	public function commentCheck($params = array(), $server_params = array())
	{
		return new CommentCheckResult($this->callSpamMethod(self::VERB_COMMENT_CHECK, $params, $server_params));
	}

	/**
	 * Submit a comment as spam. This must use the same parameters as those used when checking the
	 * comment with commetnCheck.
	 * See the Akismet API documentation for full details:
	 * https://akismet.com/development/api/#comment-check.
	 * Returns a valid SubmitSpamResult object or throws an exception.
	 *
	 * @param string[] $params
	 *        	User IP, User-Agent, the message, etc. See the Akismet API
	 *        	documentation for details.
	 * @param string[] $server_params
	 *        	This can just be $_SERVER, if you have access to it
	 * @return \Gothick\AkismetClient\Result\SubmitSpamResult
	 */
	public function submitSpam($params = array(), $server_params = array())
	{
		return new SubmitSpamResult($this->callSpamMethod(self::VERB_SUBMIT_SPAM, $params, $server_params));
	}

	/**
	 * Submit a comment as ham. This must use the same parameters as those used when checking the
	 * comment with commetnCheck.
	 * See the Akismet API documentation for full details:
	 * https://akismet.com/development/api/#comment-check.
	 * Returns a valid SubmitHamResult object or throws an exception.
	 *
	 * @param string[] $params
	 *        	User IP, User-Agent, the message, etc. See the Akismet API
	 *        	documentation for details.
	 * @param string[] $server_params
	 *        	This can just be $_SERVER, if you have access to it
	 * @return \Gothick\AkismetClient\Result\SubmitHamResult
	 */
	public function submitHam($params = array(), $server_params = array())
	{
		return new SubmitHamResult($this->callSpamMethod(self::VERB_SUBMIT_HAM, $params, $server_params));
	}

	/**
	 * Common code for calling check-comment, submit-ham and submit-spam; these all
	 * work in the same way, just returning slightly different results.
	 * @param string $verb
	 * @param string[] $params
	 * @param string[] $server_params
	 * @throws \Gothick\AkismetClient\AkismetException
	 */
	protected function callSpamMethod($verb, $params, $server_params)
	{
		// comment-check, submit-spam and submit-ham all work the same way and take
		// the same arguments, so this handles them all.
		if (empty($params[ 'user_ip' ]) || empty($params[ 'user_agent' ]))
		{
			throw new AkismetException(__METHOD__ . ' requires user_ip and user_agent in $params (' . $verb . ')');
		}
		$params = array_merge($server_params, $params);
		$params = array_merge($params, [
				'blog' => $this->blog
		]);

		try
		{
			$response = $this->callApiMethod($verb, $params);
		} catch (\Exception $e)
		{
			throw new AkismetException('Unexpected exception in ' . __METHOD__ . ' (' . $verb . ')', 0, $e);
		}
		return $response;
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
	 * @throws \Gothick\AkismetClient\AkismetException
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
				throw new AkismetException("Can't call authenticated method without setting an API key in " . __METHOD__);
			}
			return "https://{$this->api_key}.rest.akismet.com/1.1/$verb";
		}
	}
}