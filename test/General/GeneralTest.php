<?php
namespace Gothick\AkismetClient\Test\General;

use \Gothick\AkismetClient\Client;
use \Gothick\AkismetClient\AkismetException;

final class GeneralTest extends \Gothick\AkismetClient\Test\TestBase
{
	public function testCantConstructWithNullBlog()
	{
		$this->expectException(AkismetException::class);
		new Client(null, 'APPNAME', 'APPVERSION');
	}
	public function testCantConstructWithNullAppname()
	{
		$this->expectException(AkismetException::class);
		new Client('http://example.com', null, 'APPVERSION');
	}
	public function testCantConstructWithNullAppversion()
	{
		$this->expectException(AkismetException::class);
		new Client('http://example.com', 'APPNAME', null);
	}
	public function testCantConstructWithEmptyBlog()
	{
		$this->expectException(AkismetException::class);
		new Client('', 'APPNAME', 'APPVERSION');
	}
	public function testCantConstructWithEmptyAppname()
	{
		$this->expectException(AkismetException::class);
		new Client('http://example.com', '', 'APPVERSION');
	}
	public function testCantConstructWithEmptyAppversion()
	{
		$this->expectException(AkismetException::class);
		new Client('http://example.com', 'APPNAME', '');
	}
	public function testCantSetNullApiKey()
	{
		$this->expectException(AkismetException::class);
		$client = new Client('http://example.com', 'APPNAME', 'APPVERSION');
		$client->setKey(null);
	}
	public function testCantSetEmptyApiKey()
	{
		$this->expectException(AkismetException::class);
		$client = new Client('http://example.com', 'APPNAME', 'APPVERSION');
		$client->setKey('');
	}
}