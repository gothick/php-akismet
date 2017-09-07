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
}
