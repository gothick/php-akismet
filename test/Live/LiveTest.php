<?php

namespace Gothick\AkismetClient\Test\Live;

abstract class LiveTest extends \Gothick\AkismetClient\Test\TestBase
{
	/**
	 * Our "Live" tests really use the live Akismet API server (there is no test server.) These
	 * tests are only run if you've set up an environment variable AKISMET_API_KEY with your Akimet
	 * API key before running PHPUnit. Typically you'll want to use a test API key, which you can
	 * get by applying to the nice Akismet people.
	 *
	 * See https://blog.akismet.com/2012/07/20/pro-tip-testing-testing/
	 *
	 * {@inheritDoc}
	 * @see \PHPUnit\Framework\TestCase::setUp()
	 */
	public function setUp()
	{
		global $AKISMET_API_KEY;
		if (empty($AKISMET_API_KEY))
		{
			$this->markTestSkipped('Skipping "live" API server tests as no test API key is configured.');
		}
	}
}