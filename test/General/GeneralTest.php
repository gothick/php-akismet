<?php
namespace Gothick\AkismetClient\Test\General;

use \Gothick\AkismetClient\Client;

final class GeneralTest extends \Gothick\AkismetClient\Test\TestBase
{
	public function testCantConstructWithNullBlog()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		new Client(null, 'APPNAME', 'APPVERSION');
	}
	public function testCantConstructWithNullAppname()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		new Client('http://example.com', null, 'APPVERSION');
	}
	public function testCantConstructWithNullAppversion()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		new Client('http://example.com', 'APPNAME', null);
	}
	public function testCantConstructWithEmptyBlog()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		new Client('', 'APPNAME', 'APPVERSION');
	}
	public function testCantConstructWithEmptyAppname()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		new Client('http://example.com', '', 'APPVERSION');
	}
	public function testCantConstructWithEmptyAppversion()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		new Client('http://example.com', 'APPNAME', '');
	}
	public function testCantSetNullApiKey()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		$client = new Client('http://example.com', 'APPNAME', 'APPVERSION');
		$client->setKey(null);
	}
	public function testCantSetEmptyApiKey()
	{
		$this->expectException(\Gothick\AkismetClient\Exception::class);
		$client = new Client('http://example.com', 'APPNAME', 'APPVERSION');
		$client->setKey('');
	}
}