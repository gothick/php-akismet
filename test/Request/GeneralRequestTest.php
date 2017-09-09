<?php

namespace Gothick\AkismetClient\Test\Request;

final class GeneralRequestTest extends \Gothick\AkismetClient\Test\TestBase
{
	public function testUserAgentSentOK()
	{
		$history_container = [];
		$guzzle_client = self::getMockGuzzleClientWithResponse(self::verifyKeyValidResponse(), $history_container);
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', null, $guzzle_client);

		$client->verifyKey('ABCD');

		$transaction = $history_container[0];
		$request = $transaction['request'];

		$this->assertTrue($request->hasHeader('User-Agent'), 'No User-Agent present');
		$user_agent = $request->getHeader('User-Agent');
		$this->assertCount(1, $user_agent, 'Multiple User-Agents present');

		$this->assertRegExp('~^@@@APPNAME@@@/###APPVERSION### \| Gothick\\\\AkismetClient/[0-9]+\.[0-9]+$~', $user_agent[0], 'User Agent in wrong format');
	}
	public function testFormEncoding()
	{
		$history_container = [];
		$guzzle_client = self::getMockGuzzleClientWithResponse(self::verifyKeyValidResponse(), $history_container);
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', null, $guzzle_client);

		$client->verifyKey('ABCD');

		$transaction = $history_container[0];
		$request = $transaction['request'];

		$this->assertTrue($request->hasHeader('Content-Type'), 'No Content-Type present');
		$this->assertEquals('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
	}
	public function testProtocolIsHttps()
	{
		$history_container = [];
		$guzzle_client = self::getMockGuzzleClientWithResponse(self::verifyKeyValidResponse(), $history_container);
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', null, $guzzle_client);

		$client->verifyKey('ABCD');

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$this->assertEquals('https', $request->getUri()->getScheme());
	}
}