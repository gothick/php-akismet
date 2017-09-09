<?php

namespace Gothick\AkismetClient\Test\Live;

use \Gothick\AkismetClient\Client;

class LiveVerifyKeyTest extends LiveTest
{
	public function testVerifyInvalidKey()
	{
		$client = new Client('http://gothick.org.uk', 'Gothick\AkismetClient Test Suite', '1.0');
		$result = $client->verifyKey('INVALID_KEY', true);
		$this->assertFalse($result->isValid());
	}

	public function testVerifyValidKey()
	{
		global $AKISMET_API_KEY;
		$client = new Client('http://gothick.org.uk', 'Gothick\AkismetClient Test Suite', '1.0');
		$result = $client->verifyKey($AKISMET_API_KEY, true);
		$this->assertTrue($result->isValid());
	}

}