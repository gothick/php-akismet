<?php

namespace Gothick\AkismetClient\Test\Request;

final class CommentCheckTest extends \Gothick\AkismetClient\Test\TestBase
{
	public function testPassesRequiredParameters()
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345'; 

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::commentCheckHamRepsonse(), $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$result = $client->commentCheck($params, []);
		$this->assertInstanceOf(\Gothick\AkismetClient\CommentCheckResult::class, $result, 'Unexpected class returned from commentCheck');

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

	public function testPassesExtraParameters()
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::commentCheckHamRepsonse(), $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'special_chars' => '(*^&£($£^@*&*(£@"$&\')',
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8',
				'random' => 'g6Wh(FbMQ&G=Wx=gxtZ$Vx?ed#gfenAYKLXQVAZiY*VNyV&bLuxD+PZVjjEccXT$x'
		];

		$result = $client->commentCheck($params, []);
		$this->assertInstanceOf(\Gothick\AkismetClient\CommentCheckResult::class, $result, 'Unexpected class returned from commentCheck');

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$request_vars = \GuzzleHttp\Psr7\parse_query($request->getBody(), true);

		$this->assertArrayHasKey('special_chars', $request_vars);
		$this->assertArrayHasKey('random', $request_vars);
		$this->assertEquals($request_vars['special_chars'], '(*^&£($£^@*&*(£@"$&\')', 'Client did not send special_chars');
		$this->assertEquals($request_vars['random'], 'g6Wh(FbMQ&G=Wx=gxtZ$Vx?ed#gfenAYKLXQVAZiY*VNyV&bLuxD+PZVjjEccXT$x', 'Client did not send random');
	}

	public function testPassesServerParameters()
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::commentCheckHamRepsonse(), $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'CLASHING_NAME' => 'should win in name clash',
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];
		$server_params = [
				'SERVER_SOFTWARE' => 'Apache/2.4.18 (Ubuntu)',
				'SERVER_NAME' => 'vagrant.localhost',
				'CLASHING_NAME' => 'should be overridden'
		];

		$result = $client->commentCheck($params, $server_params);

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$request_vars = \GuzzleHttp\Psr7\parse_query($request->getBody(), true);

		$this->assertArrayHasKey('CLASHING_NAME', $request_vars);
		$this->assertArrayHasKey('SERVER_NAME', $request_vars);
		$this->assertArrayHasKey('SERVER_SOFTWARE', $request_vars);
		$this->assertEquals($request_vars['CLASHING_NAME'], 'should win in name clash', 'Wrong CLASHING_NAME passed through');
		$this->assertEquals($request_vars['SERVER_NAME'], 'vagrant.localhost', 'Client did not send correct SERVER_NAME');
		$this->assertEquals($request_vars['SERVER_SOFTWARE'], 'Apache/2.4.18 (Ubuntu)', 'Client did not send correct SERVER_SOFTWARE');
	}

	public function testFailsOnInvalid200Responses()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		$guzzle_client = self::getMockGuzzleClientWithResponse(self::unexpected200Response());
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', 'TESTKEY', $guzzle_client);
		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];
		$client->commentCheck($params);
	}
}
