<?php
namespace Gothick\AkismetClient\Test\Request;

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
}