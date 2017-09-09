<?php

namespace Gothick\AkismetClient\Test\Request;

final class SpamMethodsTest extends \Gothick\AkismetClient\Test\TestBase
{
	public function normalResponseProvider()
	{
		return [
				['commentCheck', self::commentCheckHamResponse(), \Gothick\AkismetClient\Result\CommentCheckResult::class, 'comment-check'],
				['submitSpam', self::submitSpamResponse(), \Gothick\AkismetClient\Result\SubmitSpamResult::class, 'submit-spam'],
				['submitHam',  self::submitHamResponse(), \Gothick\AkismetClient\Result\SubmitHamResult::class, 'submit-ham']
		];
	}

	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testPassesRequiredParameters($method, $response, $result_class)
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse($response, $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$result = $client->{$method}($params, []);
		$this->assertInstanceOf($result_class, $result, 'Unexpected class returned from commentCheck');

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

	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testPassesExtraParameters($method, $response, $result_class)
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse($response, $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'special_chars' => '(*^&£($£^@*&*(£@"$&\')',
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8',
				'random' => 'g6Wh(FbMQ&G=Wx=gxtZ$Vx?ed#gfenAYKLXQVAZiY*VNyV&bLuxD+PZVjjEccXT$x'
		];

		$result = $client->{$method}($params, []);
		$this->assertInstanceOf($result_class, $result, 'Unexpected class returned from commentCheck');

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$request_vars = \GuzzleHttp\Psr7\parse_query($request->getBody(), true);

		$this->assertArrayHasKey('special_chars', $request_vars);
		$this->assertArrayHasKey('random', $request_vars);
		$this->assertEquals($request_vars['special_chars'], '(*^&£($£^@*&*(£@"$&\')', 'Client did not send special_chars');
		$this->assertEquals($request_vars['random'], 'g6Wh(FbMQ&G=Wx=gxtZ$Vx?ed#gfenAYKLXQVAZiY*VNyV&bLuxD+PZVjjEccXT$x', 'Client did not send random');
	}

	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testPassesServerParameters($method, $response, $result_class)
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse($response, $history_container);
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

		$result = $client->{$method}($params, $server_params);
		$this->assertInstanceOf($result_class, $result);

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

	public function spamMethodsProvider()
	{
		return [
				['commentCheck'],
				['submitHam'],
				['submitSpam']
		];
	}
	/**
	 * @dataProvider spamMethodsProvider
	 */
	public function testFailsOnInvalid200Responses($method)
	{
		$this->expectException(\Gothick\AkismetClient\AkismetException::class);
		$guzzle_client = self::getMockGuzzleClientWithResponse(self::unexpected200Response());
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', 'TESTKEY', $guzzle_client);
		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];
		$client->$method($params);
	}

	/**
	 * @dataProvider spamMethodsProvider
	 */
	public function testDebugHelpMessage($method)
	{
		$this->expectException(\Gothick\AkismetClient\AkismetException::class);
		$guzzle_client = self::getMockGuzzleClientWithResponse(self::badParametersResponse());
		$client = new \Gothick\AkismetClient\Client('http://example.com', '@@@APPNAME@@@', '###APPVERSION###', 'TESTKEY', $guzzle_client);
		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];
		$client->{$method}($params);
	}

	public function testSpamResponse()
	{
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::commentCheckSpamResponse());
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$result = $client->commentCheck($params, []);
		$this->assertInstanceOf(\Gothick\AkismetClient\Result\CommentCheckResult::class, $result, 'Unexpected class returned from commentCheck');
		$this->assertTrue($result->isSpam());
		$this->assertFalse($result->isBlatantSpam());
	}

	public function testBlatantSpamResponse()
	{
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::commentCheckBlatantSpamResponse());
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$result = $client->commentCheck($params, []);
		$this->assertInstanceOf(\Gothick\AkismetClient\Result\CommentCheckResult::class, $result, 'Unexpected class returned from commentCheck');
		$this->assertTrue($result->isSpam());
		$this->assertTrue($result->isBlatantSpam());
	}

	public function testHamResponse()
	{
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse(self::commentCheckHamResponse());
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$result = $client->commentCheck($params, []);
		$this->assertInstanceOf(\Gothick\AkismetClient\Result\CommentCheckResult::class, $result, 'Unexpected class returned from commentCheck');
		$this->assertFalse($result->isSpam());
		$this->assertFalse($result->isBlatantSpam());
	}

	/**
	 * We don't need to do much for submitHam and submitSpam responses, because they just return a simple
	 * string no matter what, according to the API. Let's just make sure we get the right result class
	 * back in all cases.
	 * @dataProvider normalResponseProvider
	 */
	public function testResponseClass($method, $response, $result_class)
	{
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse($response);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$result = $client->{$method}($params, []);
		$this->assertInstanceOf($result_class, $result, 'Unexpected class returned from ' . $method);
	}

	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testUsesApiKey($method, $response, $result_class)
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse($response, $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$client->{$method}($params, []);

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$this->assertTrue($request->hasHeader('Host'));
		$this->assertEquals(strtolower($test_key) . '.rest.akismet.com', $request->getHeaderLine('Host'));
	}

	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testUsesApiKeyWhenSetManually($method, $response, $result_class)
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';

		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse($response, $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', null, $guzzle_client);
		$client->setKey($test_key);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$client->{$method}($params, []);

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$this->assertTrue($request->hasHeader('Host'));
		$this->assertEquals(strtolower($test_key) . '.rest.akismet.com', $request->getHeaderLine('Host'));
	}

	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testFailsWithoutApiKey($method, $response, $result_class)
	{
		$this->expectException(\Gothick\AkismetClient\AkismetException::class);
		$test_blog_url = 'http://example.com';

		$test_key = null;

		$guzzle_client = self::getMockGuzzleClientWithResponse($response);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$client->{$method}($params, []);
	}
	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testFailsWithoutUserIp($method, $response, $result_class)
	{
		$this->expectException(\Gothick\AkismetClient\AkismetException::class);
		$test_blog_url = 'http://example.com';

		$test_key = null;

		$guzzle_client = self::getMockGuzzleClientWithResponse($response);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$client->{$method}($params, []);
	}
	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testFailsWithoutUserAgent($method, $response, $result_class)
	{
		$this->expectException(\Gothick\AkismetClient\AkismetException::class);
		$test_blog_url = 'http://example.com';

		$test_key = null;

		$guzzle_client = self::getMockGuzzleClientWithResponse($response);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', $test_key, $guzzle_client);

		$params = [
				'user_ip' => '123.234.123.254'
		];

		$client->{$method}($params, []);
	}

	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testRestMethod($method, $response, $result_class)
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';
		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse($response, $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', null, $guzzle_client);
		$client->setKey($test_key);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$client->{$method}($params, []);

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$this->assertEquals('POST', $request->getMethod());
	}
	/**
	 * @dataProvider normalResponseProvider
	 */
	public function testRestVerb($method, $response, $result_class, $verb_should_be)
	{
		$history_container = [];
		$test_blog_url = 'http://example.com';
		$test_key = 'PRECONFABCDEF12345';

		$guzzle_client = self::getMockGuzzleClientWithResponse($response, $history_container);
		$client = new \Gothick\AkismetClient\Client($test_blog_url, '@@@APPNAME@@@', '###APPVERSION###', null, $guzzle_client);
		$client->setKey($test_key);

		$params = [
				'user_ip' => '123.234.123.254',
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8'
		];

		$client->{$method}($params, []);

		$transaction = $history_container[0];
		$request = $transaction['request'];
		$this->assertEquals('/1.1/' . $verb_should_be, $request->getUri()->getPath());
	}
}

