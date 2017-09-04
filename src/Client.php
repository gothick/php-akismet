<?php
namespace Gothick\AkismetClient;

class Client
{
	/**
	 * Akismet API key
	 * @var string
	 */
	private $api_key;
	private $guzzle_client;
	private $blog;

	/**
	 * Create an Akismet client.
	 *
	 * @param string $api_key
	 */
	public function __construct ($api_key, $blog)
	{
		$this->api_key = $api_key;
		$this->blog = $blog;
		// Well, there'd be no point in creating an instance of us unless we were going
		// to do some work, so we might as well warm up the Guzzle client here.
		$this->guzzle_client = new \GuzzleHttp\Client();
	}

	public function verifyKey($api_key = null)
	{
		$verified = false;
		$error = '';
		$key_to_verify = empty($api_key) ? $this->api_key : $api_key;

		$response = $this->guzzle_client->request(
			'POST', 
			$this->apiUri('verify-key'),
			[ 'form_params' => [
					"key" => $key_to_verify,
					"blog" => $this->blog
				]
			]
		);
		if ($response->getStatusCode() == 200) {
			$result = (string) $response->getBody();
			if ($result == 'valid') {
				$verified = true;
			} else {
				$error = "200 Response: $result";
			}
		} else {
			$error = (string) $response->getStatusCode();
			if ($response->hasHeader('X-akismet-debug-help')) {
				$error .= ': ' . $response->getHeader('X-akismet-debug-help');
			}
		}
		if (!$verified) {
			throw new \Exception(__METHOD__ . ': ' . $error);
		}
		return true;
	}

	public function commentCheck(
		$blog,
		$user_ip,
		$user_agent,
		$referrer,  /* (note spelling) */
		$permalink,
		$comment_type, /* comment, forum-post, reply, blog-post, contact-form, signup, message */
		$comment_author,
		$comment_author_email,
		$comment_author_url,
		$comment_content,
		$comment_date_gmt,
		$comment_post_modified_gmt,
		$blog_lang,
		$blog_charset,
		$user_role, /* The user role of the user who submitted the comment. This is an optional parameter. If you set it to “administrator”, Akismet will always return false. */
		$server_variables,
		$is_test
	)
	{
		// TODO
	}

	private function apiUri($method) 
	{
		if ($method == 'verify-key') {
			return "https://rest.akismet.com/1.1/verify-key";
		} else {
			return "https://{$this->api_key}.rest.akismet.com/1.1/$method";
		}
	}

	// TODO: Setting your user agent If possible, your user agent string should always use the following format: Application Name/Version | Plugin Name/Version
	// e.g. WordPress/4.4.1 | Akismet/3.1.7
	// https://akismet.com/development/api/#detailed-docs
	public function isSpam ()
	{
		die(__METHOD__ . ' unimplemented');
	}
}