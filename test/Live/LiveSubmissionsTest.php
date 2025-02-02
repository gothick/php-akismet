<?php

namespace Gothick\AkismetClient\Test\Live;

use \Gothick\AkismetClient\Client;

class LiveSubmissionsTest extends LiveTestCase
{
	public function testSubmitSpam()
	{
		global $AKISMET_API_KEY;
		$client = new Client('http://gothick.org.uk', 'Gothick\AkismetClient Test Suite', '1.0', $AKISMET_API_KEY);
		$params = [
			// Don't do any training
			'is_test' => '1',
			'user_ip' => '123.234.123.254',
			'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8',
			'comment_type' => 'forum-post',
			// This magic value should ensure we always get a spam result back.
			'comment_author' => 'viagra-test-123',
			'comment_author_email' => 'gothick+akismetphp@gothick.org.uk',
			'comment_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras at elit nibh, in pretium tellus. Donec id dui mi. Nam malesuada, velit sed porta mollis, dolor felis eleifend diam, nec convallis orci libero eget augue. Vestibulum quis pretium tellus. Morbi nulla nulla, tempus congue viverra id, iaculis ultricies lorem. Fusce leo turpis, luctus ac dignissim ac, posuere vitae odio.'
		];

		// Grabbed as an example from a test file on an old server of mine.
		$server = array (
				'USER' => 'www-data',
				'HOME' => '/var/www',
				'SCRIPT_NAME' => '/server.php',
				'REQUEST_URI' => '/server.php',
				'QUERY_STRING' => '',
				'REQUEST_METHOD' => 'GET',
				'SERVER_PROTOCOL' => 'HTTP/1.1',
				'GATEWAY_INTERFACE' => 'CGI/1.1',
				'REMOTE_PORT' => '62965',
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
				'HTTP_COOKIE' => 'wordpress_logged_in_82382f5dd82920cb8aa8b6b5120e473a=gothick%7C1506120131%7CYg1mIzPqoGxnQ1iqa3Ujj3FsfJkEWiubdyMPWljInZi%7C79c60dd68ac4155760a6bcd7c095d6bc96bdf5bd50dbc68e0aa80933941bd226; wordpress_test_cookie=WP+Cookie+check; _ga=GA1.3.588139408.1475604730; _gid=GA1.3.608970456.1504910498; tk_ni=1053855; __utma=192419900.588139408.1475604730.1477071701.1477085128.5',
				'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
				'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'HTTP_HOST' => 'oldtweets.gothick.org.uk',
				'SCRIPT_URI' => 'http://oldtweets.gothick.org.uk/server.php',
				'SCRIPT_URL' => '/server.php',
				'FCGI_ROLE' => 'RESPONDER',
				'PHP_SELF' => '/server.php',
				'REQUEST_TIME_FLOAT' => 1504989908.6036501,
				'REQUEST_TIME' => 1504989908,
		);

		$result = $client->submitSpam($params, $server);
		$this->assertInstanceOf(\Gothick\AkismetClient\Result\SubmitSpamResult::class, $result);
	}
	public function testSubmitHam()
	{
		global $AKISMET_API_KEY;
		$client = new Client('http://gothick.org.uk', 'Gothick\AkismetClient Test Suite', '1.0', $AKISMET_API_KEY);
		$params = [
				// Don't do any training
				'is_test' => '1',
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8',
				'comment_type' => 'forum-post',
				'comment_author' => 'Matt Gibson',
				'comment_author_email' => 'gothick+akismetphp@gothick.org.uk',
				'comment_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras at elit nibh, in pretium tellus. Donec id dui mi. Nam malesuada, velit sed porta mollis, dolor felis eleifend diam, nec convallis orci libero eget augue. Vestibulum quis pretium tellus. Morbi nulla nulla, tempus congue viverra id, iaculis ultricies lorem. Fusce leo turpis, luctus ac dignissim ac, posuere vitae odio.',
				// This magic value should ensure we always get a ham result back.
				'user_role' => 'administrator'
		];

		// Grabbed as an example from a test file on an old server of mine.
		$server = array (
				'USER' => 'www-data',
				'HOME' => '/var/www',
				'SCRIPT_NAME' => '/server.php',
				'REQUEST_URI' => '/server.php',
				'QUERY_STRING' => '',
				'REQUEST_METHOD' => 'GET',
				'SERVER_PROTOCOL' => 'HTTP/1.1',
				'GATEWAY_INTERFACE' => 'CGI/1.1',
				'REMOTE_PORT' => '62965',
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
				'HTTP_COOKIE' => 'wordpress_logged_in_82382f5dd82920cb8aa8b6b5120e473a=gothick%7C1506120131%7CYg1mIzPqoGxnQ1iqa3Ujj3FsfJkEWiubdyMPWljInZi%7C79c60dd68ac4155760a6bcd7c095d6bc96bdf5bd50dbc68e0aa80933941bd226; wordpress_test_cookie=WP+Cookie+check; _ga=GA1.3.588139408.1475604730; _gid=GA1.3.608970456.1504910498; tk_ni=1053855; __utma=192419900.588139408.1475604730.1477071701.1477085128.5',
				'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
				'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'HTTP_HOST' => 'oldtweets.gothick.org.uk',
				'SCRIPT_URI' => 'http://oldtweets.gothick.org.uk/server.php',
				'SCRIPT_URL' => '/server.php',
				'FCGI_ROLE' => 'RESPONDER',
				'PHP_SELF' => '/server.php',
				'REQUEST_TIME_FLOAT' => 1504989908.6036501,
				'REQUEST_TIME' => 1504989908,
		);

		$result = $client->submitHam($params, $server);
		$this->assertInstanceOf(\Gothick\AkismetClient\Result\SubmitHamResult::class, $result);
	}
}
