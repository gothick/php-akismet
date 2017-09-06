<?php

namespace Gothick\AkismetClient\Test\Request;

final class CommentCheckTest extends \Gothick\AkismetClient\Test\TestBase
{
	public function testPassesRequiredParameters()
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345'; 

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::verifyKeyValidResponse(), $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$result = $client->commentCheck($params, []);
		$this->assertInstanceOf(\Gothick\AkismetClient\ClientResult::class, $result);
		var_dump($result);

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$request_vars = \GuzzleHttp\Psr7\parse_query($request->getBody(), true);

		$this->assertArrayHasKey('blog', $request_vars);
		$this->assertArrayHasKey('user_ip', $request_vars);
		$this->assertArrayHasKey('user_agent', $request_vars);
		$this->assertEquals($test_blog_url, $request_vars['blog'], 'Client did not send correct blog');
		$this->assertEquals($params['user_ip'], $request_vars['user_ip'], 'Client did not send correct user_ip');
		$this->assertEquals($params['user_agent'], $request_vars['user_agent'], 'Client did not send correct user_agent');
	}

	public function testFailsOnInvalid200Responses()
	{
		$this->markTestIncomplete();
	}

	public function testCanVerifyConfiguredKey()
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345!&$*$&???##'; // If that's not properly URL-encoded, we'll know about it!

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::verifyKeyValidResponse(), $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$this->assertTrue($client->verifyKey(), 'Incorrect result verifying key');

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$request_vars = \GuzzleHttp\Psr7\parse_query($request->getBody(), true);
		$this->assertEquals($test_key, $request_vars['key'], 'Client did not send correct key');
		$this->assertEquals($test_blog_url, $request_vars['blog'], 'Client did not send correct blog');
	}

	public function testCanVerifyArbitraryKey()
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$preconfigured_test_key = 'PRECONFABCDEF12345!&$*$&???##'; // If that's not properly URL-encoded, we'll know about it!
		$arbitrary_test_key = 'ARBABCDEF12345!&$*$&???##'; // If that's not properly URL-encoded, we'll know about it!

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::verifyKeyValidResponse(), $history_container);
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', $preconfigured_test_key, $guzzle_client);

		$this->assertTrue($client->verifyKey($arbitrary_test_key), 'Incorrect result verifying key');

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$request_vars = \GuzzleHttp\Psr7\parse_query($request->getBody(), true);
		$this->assertEquals($arbitrary_test_key, $request_vars['key'], 'Client did not send correct key');
		$this->assertEquals($test_blog_url, $request_vars['blog'], 'Client did not send correct blog');
	}

	public function testCanVerifyConfiguredBadKey()
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345!&$*$&???##'; // If that's not properly URL-encoded, we'll know about it!

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::verifyKeyInvalidResponse(), $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$this->assertFalse($client->verifyKey(), 'Incorrect result verifying key');

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$request_vars = \GuzzleHttp\Psr7\parse_query($request->getBody(), true);
		$this->assertEquals($test_key, $request_vars['key'], 'Client did not send correct key');
		$this->assertEquals($test_blog_url, $request_vars['blog'], 'Client did not send correct blog');
	}

	public function testVerifyKeyThrowsExceptionOnUnexpectedResult()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		$guzzle_client = self::getMockGuzzleClientWithResponse(self::verifyKeyUnexpected500Response());
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', 'ABCDEF', $guzzle_client);
		$this->assertFalse($client->verifyKey());
	}

	public function testVerifyKeyCallIsUnauthenticated()
	{
		$history_container = [];

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::verifyKeyValidResponse(), $history_container);
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', 'ABCDEF123', $guzzle_client);

		$client->verifyKey();

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$this->assertTrue($request->hasHeader('Host'));
		$this->assertCount(1, $request->getHeader('Host'));
		$host = $request->getHeader('Host');

		$this->assertEquals($host[0], 'rest.akismet.com');
	}
}
