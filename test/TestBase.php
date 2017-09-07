<?php

namespace Gothick\AkismetClient\Test;

use \PHPUnit\Framework\TestCase;

use \GuzzleHttp\Handler\MockHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Psr7\Response;
use \GuzzleHttp\Middleware;

abstract class TestBase extends TestCase
{
	protected static function getMockGuzzleClientWithResponse($response, &$history_container = null)
	{
		$mock_handler = new MockHandler([$response]);
		$handler_stack = HandlerStack::create($mock_handler);

		if (isset($history_container)) {
			$history_handler = Middleware::history($history_container);
			$handler_stack->push($history_handler);
		}
		$guzzle_client = new \GuzzleHttp\Client(['handler' => $handler_stack]);
		return $guzzle_client;
	}

	protected static function verifyKeyValidResponse()
	{
		return new Response(
				200,
				[
						'Connection' => 'keep-alive',
						'Content-Length' => '5',
						'Content-Type' => 'text/plain; charset=utf-8',
						'Date' => 'Wed, 06 Sep 2017 11:54:28 GMT',
						'Server' => 'nginx',
						'X-akismet-alert-code' => '10007',
						'X-akismet-alert-msg' => "Howdy! We're glad that you're enjoying Akismet! It looks like your Akismet Plus subscription is currently being used on more sites than it supports. You will simply need to access your Akismet account and update your subscription. Please click on the link below for some further details."
				],
				'valid'
				);
	}

	protected static function verifyKeyInvalidResponse()
	{
		return new Response(
				200,
				[
						'Connection' => 'keep-alive',
						'Content-Length' => '7',
						'Content-Type' => 'text/plain; charset=utf-8',
						'Date' => 'Wed, 06 Sep 2017 11:54:28 GMT',
						'Server' => 'nginx'
				],
				'invalid'
				);
	}

	protected static function commentCheckHamRepsonse()
	{
		return new Response(
				200,
				[
						'Connection' => 'keep-alive',
						'Content-Length' => '5',
						'Content-Type' => 'text/plain; charset=utf-8',
						'Date' => 'Wed, 06 Sep 2017 11:54:28 GMT',
						'Server' => 'nginx',
						'X-akismet-alert-code' => '10007',
						'X-akismet-alert-msg' => "Howdy! We're glad that you're enjoying Akismet! It looks like your Akismet Plus subscription is currently being used on more sites than it supports. You will simply need to access your Akismet account and update your subscription. Please click on the link below for some further details.",
						// TODO: What *is* the X-akismet-guid? Can we do anything with it?
						'X-akismet-guid' => '8b3d95a751ebad069dfcee9a3057fc40'
				],
				'false'
		);
	}

	protected static function commentCheckSpamResponse()
	{
		return new Response(
				200,
				[
						'Connection' => 'keep-alive',
						'Content-Length' => '4',
						'Content-Type' => 'text/plain; charset=utf-8',
						'Date' => 'Wed, 06 Sep 2017 11:54:28 GMT',
						'Server' => 'nginx',
						'X-akismet-alert-code' => '10007',
						'X-akismet-alert-msg' => "Howdy! We're glad that you're enjoying Akismet! It looks like your Akismet Plus subscription is currently being used on more sites than it supports. You will simply need to access your Akismet account and update your subscription. Please click on the link below for some further details.",
						// TODO: What *is* the X-akismet-guid? Can we do anything with it?
						'X-akismet-guid' => '8b3d95a751ebad069dfcee9a3057fc40'
				],
				'true'
		);
	}

	protected static function unexpected200Response()
	{
		return new Response(
				200,
				[
						'Connection' => 'keep-alive',
						'Content-Length' => '9',
						'Content-Type' => 'text/plain; charset=utf-8',
						'Date' => 'Wed, 06 Sep 2017 11:54:28 GMT',
						'Server' => 'nginx'
				],
				'argleflap'
				);
	}
	protected static function serverErrorResponse()
	{
		return new Response(
				500,
				[
						'Connection' => 'keep-alive',
						'Content-Length' => '21',
						'Content-Type' => 'text/plain; charset=utf-8',
						'Date' => 'Wed, 06 Sep 2017 11:54:28 GMT',
						'Server' => 'nginx'
				],
				'Internal Server Error'
				);
	}
	
}