<?php

namespace Gothick\AkismetClient\Test\Request;

final class VerifyKeyRequestTest extends \Gothick\AkismetClient\Test\TestBase
{
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

	public function testVerifyKeyThrowsExceptionOnServerError()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		$guzzle_client = self::getMockGuzzleClientWithResponse(self::serverErrorResponse());
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', 'ABCDEF', $guzzle_client);
		$this->assertFalse($client->verifyKey());
	}
	
	public function testVerifyKeyThrowsExceptionOnUnexpected200Response()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		$guzzle_client = self::getMockGuzzleClientWithResponse(self::unexpected200Response());
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
	public function testRestVerb()
	{
		$this->markTestIncomplete();
	}
}
