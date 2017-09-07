<?php

require_once (__DIR__ . '/../vendor/autoload.php');

// This is a quick dump of a vaguely-typical $_SERVER contents
$server_array = array(
		'USER' => 'www-data',
		'HOME' => '/var/www',
		'SCRIPT_NAME' => '/server.php',
		'REQUEST_URI' => '/server.php',
		'QUERY_STRING' => '',
		'REQUEST_METHOD' => 'GET',
		'SERVER_PROTOCOL' => 'HTTP/1.1',
		'GATEWAY_INTERFACE' => 'CGI/1.1',
		'REMOTE_PORT' => '53885',
		'SCRIPT_FILENAME' => '/var/www/sites/gothick.org.uk/oldtweets.gothick.org.uk/html///server.php',
		'SERVER_ADMIN' => '[no address given]',
		'CONTEXT_DOCUMENT_ROOT' => '/var/www/sites/gothick.org.uk/oldtweets.gothick.org.uk/html/',
		'CONTEXT_PREFIX' => '',
		'REQUEST_SCHEME' => 'http',
		'DOCUMENT_ROOT' => '/var/www/sites/gothick.org.uk/oldtweets.gothick.org.uk/html/',
		'REMOTE_ADDR' => '81.174.144.111',
		'SERVER_PORT' => '80',
		'SERVER_ADDR' => '172.31.26.151',
		'SERVER_NAME' => 'oldtweets.gothick.org.uk',
		'SERVER_SOFTWARE' => 'Apache',
		'SERVER_SIGNATURE' => '<address>Apache Server at oldtweets.gothick.org.uk Port 80</address>
',
		'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
		'HTTP_CONNECTION' => 'keep-alive',
		'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
		'HTTP_ACCEPT_LANGUAGE' => 'en-gb',
		'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8',
		'HTTP_COOKIE' => '_ga=GA1.3.588139408.1475604730; wordpress_logged_in_82382f5dd82920cb8aa8b6a5120e473a=gothick%7C1504810209%7C7Um407vKMaHtvaEQp4dRfb8erY1p9EvcV75GUr3rKL5%7Cda0258d050b85b9c76d5ed2e9e41c63fec0a015982f24781c4f6ab7fe63acb76; tk_ni=1053855; __utma=192419900.588139408.1475604730.1477071701.1477085128.5',
		'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
		'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'HTTP_HOST' => 'oldtweets.gothick.org.uk',
		'SCRIPT_URI' => 'http://oldtweets.gothick.org.uk/server.php',
		'SCRIPT_URL' => '/server.php',
		'FCGI_ROLE' => 'RESPONDER',
		'PHP_SELF' => '/server.php',
		'REQUEST_TIME_FLOAT' => 1504797157.3035829,
		'REQUEST_TIME' => 1504797157,
);

$container = [];
$history = \GuzzleHttp\Middleware::history($container);
$stack = \GuzzleHttp\HandlerStack::create();
$stack->push($history);

$guzzle_client = new \GuzzleHttp\Client([
		'handler' => $stack
]);

$akismet_client = new \Gothick\AkismetClient\Client('http://gothick.org.uk',
		'Test App', 'v1.4', '359ee33b40e0', $guzzle_client);

$result = $akismet_client->verifyApiKey();
var_dump($result);

$result = $akismet_client->commentCheck('81.174.144.111',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12',
		[
				'comment_type' => 'forum-post',
				'comment_author' => 'Matt Gibson',
				'comment_author_email' => 'gothick@gothick.org.uk',
				'comment_content' => 'Hi. Just testing.'
		], $server_array, 'user', true);

var_dump($result);

$result = $akismet_client->commentCheck('81.174.144.111',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12',
		[
				'comment_type' => 'forum-post',
				'comment_author' => 'viagra-test-123', // Triggers spam result
				'comment_author_email' => 'gothick@gothick.org.uk',
				'comment_content' => 'Hi. Just testing.'
		], $server_array, 'user', true);

var_dump($result);


foreach ($container as $transaction)
{
	echo $transaction['request']->getMethod();
	//> GET, HEAD
	if ($transaction['response'])
	{
		echo $transaction['response']->getStatusCode();
		//> 200, 200
	}
	elseif ($transaction['error'])
	{
		echo $transaction['error'];
		//> exception
	}
	var_dump($transaction['request']);

	$request_body = (string) $transaction['request']->getBody();
	var_dump($request_body);

	/*
	$retrieved_params = array();
	foreach (explode('&', $request_body) as $chunk) {
		$param = explode("=", $chunk);
		if ($param)
		{
			$retrieved_params[urldecode($param[0])] = urldecode($param[1]);
		}
	}
	print_r($retrieved_params);
	*/

	// Take the body we sent and decode it back into an array for checking.
	var_dump(GuzzleHttp\Psr7\parse_query($request_body, true));
	// var_dump($transaction['options']);
	//> dumps the request options of the sent request.
}


