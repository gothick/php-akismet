<?php
require_once(dirname(__FILE__) . '/../vendor/autoload.php');

$container = [];
$history = \GuzzleHttp\Middleware::history($container);
$stack = \GuzzleHttp\HandlerStack::create();
$stack->push($history);

$guzzle_client = new \GuzzleHttp\Client(['handler' => $stack]);

$akismet_client = new \Gothick\AkismetClient\Client(
	'http://gothick.org.uk', 
	'Test App', 
	'v1.4', 
	'359ee33b40e0',
    $guzzle_client
);


$result = $akismet_client->verifyKey();
var_dump($result);

$result = $akismet_client->commentCheck(
	'81.174.144.111',
	'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12',
	[
		'comment_type' => 'forum-post',
		'comment_author' => 'Matt Gibson',
		'comment_author_email' => 'gothick@gothick.org.uk',
		'comment_content' => 'Hi. Just testing.'
	],
	array(),
	'user',
	true);

var_dump($result);



$result = $akismet_client->commentCheck(
	'81.174.144.111',
	'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12',
	[
		'comment_type' => 'forum-post',
		'comment_author' => 'viagra-test-123', // Triggers spam result
		'comment_author_email' => 'gothick@gothick.org.uk',
		'comment_content' => 'Hi. Just testing.'
	],
	array(),
	'user',
	true);

var_dump($result);

foreach ($container as $transaction) {
    echo $transaction['request']->getMethod();
    //> GET, HEAD
    if ($transaction['response']) {
        echo $transaction['response']->getStatusCode();
        //> 200, 200
    } elseif ($transaction['error']) {
        echo $transaction['error'];
        //> exception
    }
    var_dump($transaction['request']);
    // var_dump($transaction['options']);
    //> dumps the request options of the sent request.
}


