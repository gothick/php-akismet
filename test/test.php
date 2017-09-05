<?php

require_once (__DIR__ . '/../vendor/autoload.php');

// This is a quick dump of a vaguely-typical $_SERVER contents
$server_array = array(
		'HTTP_HOST' => 'vagrant.localhost',
		'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
		'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8',
		'HTTP_ACCEPT_LANGUAGE' => 'en-gb',
		'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
		'HTTP_CONNECTION' => 'keep-alive',
		'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
		'SERVER_SIGNATURE' => '<address>Apache/2.4.18 (Ubuntu) Server at vagrant.localhost Port 80</address>
		  ',
		'SERVER_SOFTWARE' => 'Apache/2.4.18 (Ubuntu)',
		'SERVER_NAME' => 'vagrant.localhost',
		'SERVER_ADDR' => '192.168.4.14',
		'SERVER_PORT' => '80',
		'REMOTE_ADDR' => '192.168.4.1',
		'DOCUMENT_ROOT' => '/var/www/html',
		'REQUEST_SCHEME' => 'http',
		'CONTEXT_PREFIX' => '',
		'CONTEXT_DOCUMENT_ROOT' => '/var/www/html',
		'SERVER_ADMIN' => 'webmaster@localhost',
		'SCRIPT_FILENAME' => '/var/www/html/index.php',
		'REMOTE_PORT' => '64636',
		'GATEWAY_INTERFACE' => 'CGI/1.1',
		'SERVER_PROTOCOL' => 'HTTP/1.1',
		'REQUEST_METHOD' => 'GET',
		'QUERY_STRING' => '',
		'REQUEST_URI' => '/',
		'SCRIPT_NAME' => '/index.php',
		'PHP_SELF' => '/index.php',
		'REQUEST_TIME_FLOAT' => 1504627458.339,
		'REQUEST_TIME' => 1504627458
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

$result = $akismet_client->verifyKey();
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


