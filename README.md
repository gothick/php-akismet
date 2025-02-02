# php-akismet

[![Build Status](https://travis-ci.org/gothick/php-akismet.svg?branch=master)](https://travis-ci.org/gothick/php-akismet)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gothick/php-akismet/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gothick/php-akismet/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/gothick/php-akismet/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/gothick/php-akismet/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/gothick/php-akismet/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gothick/php-akismet/build-status/master)

A simple PHP Akismet client.

* PSR-4 autoloading
* Composer-friendly
* Uses Guzzle as its http client
* Exposes all Akismet methods and return values

# Simple Usage

## Spam checking

Uses Akismet's `comment-check` API method:

```php
    $client = new \Gothick\AkismetClient\Client(
        "http://example.com",   // Your website's URL (this becomes Akismet's "blog" parameter)
        "Example Forum",        // Your website or app's name (Used in the User-Agent: header when talking to Akismet)
        "1.2.3",                // Your website or app's software version (Used in the User-Agent: header when talking to Akismet)
        "YOUR KEY HERE"         // Your Akismet API key
    );

    // See https://akismet.com/development/api/#comment-check for all available parameters
    $params = [
        "user_ip" => "203.0.113.4", // IP address of person posting the comment
        "user_agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8", // User-Agent header of the commenter
        "comment_type" => "forum-post",
        "comment_author" => "Spammy McSpamface",
        "comment_content" => "I'm an evil spammy message"
    ]

    // $result will be of type \Gothick\AkismetClient\Result\CommentCheckResult.php
    // Akismet really wants to see your $_SERVER variables. "This data is highly useful to
    // Akismet. How the submitted content interacts with the server can be very telling,
    // so please include as much of it as possible." But obviously, if you're worried,
    // you could filter anything senstive out and send in a pared-down array instead.
    $result = $client->commentCheck($params, $_SERVER);

    $is_spam = $result->isSpam(); // Boolean
```

## More advanced usage

```php
    // ...get $result from client as above...
    // If it's blatant spam that Akismet thinks you can discard without human
    // intervention (see https://blog.akismet.com/2014/04/23/theres-a-ninja-in-your-akismet/)
    $is_blatant_spam = $result->isBlatantSpam();

    // Get the X-akismet-pro-tip header, if present
    if ($result->hasProTip()) {
        $pro_tip = $result->getProTip();
    }

    // Get the X-akismet-debug-help header, if present
    if ($result->hasDebugHelp()) {
        $debug_help = $result->getDebugHelp();
    }
```

## Verifying your API key

```php
    $client = new \Gothick\AkismetClient\Client(
        "http://example.com",   // Your website's URL (this becomes Akismet's "blog" parameter)
        "Example Forum",        // Your website or app's name (Used in the User-Agent: header when talking to Akismet)
        "1.2.3",                // Your website or app's software version (Used in the User-Agent: header when talking to Akismet)
        "YOUR KEY HERE"         // Your Akismet API key
    );

    // $result will be of type \Gothick\AkismetClient\Result\VerifyKeyResult
    $result = $client->verifyKey();
    $api_key_is_valid = $result->isValid(); // Boolean

    // Can also check pro tip and debug help as above.
```

## Submitting ham and spam

This client also exposes Akismet's `submit-spam` and `submit-ham` methods. Use them as
with commentCheck above, passing exactly the same parameters. See the Akismet API
documentation for more details.

```php
    $client->submitHam($params, $_SERVER);
    // OR
    $client->submitSpam($params, $_SERVER);
```

# Using a custom Guzzle client

If you have particular network transport needs, you may override the default Guzzle
client that the Akismet client uses by passing a Guzzle client as the last constructor
parameter:

    $guzzle_client = new \GuzzleHttp\Client([
        'timeout' => 10.0,
        'handler' => $my_special_handler_stack
    ]);
    $akismet_client = new \Gothick\AkismetClient\Client(
        "http://example.com",   // Your website's URL (this becomes Akismet's "blog" parameter)
        "Example Forum",        // Your website or app's name (Used in the User-Agent: header when talking to Akismet)
        "1.2.3",                // Your website or app's software version (Used in the User-Agent: header when talking to Akismet)
        "YOUR KEY HERE",        // Your Akismet API key
        $guzzle_client
    );

# Error handling

The client should either Just Work or throw `\Gothick\AkismetClient\AkismetException`,
which is an entirely trivial extension of the PHP `\Exception` base class.

# Tests

A unit test suite is provided; install the package using Composer with dev requirements,
then (you'll need PHP 8.1+ and PHPUnit 10.5+):

```sh
    php vendor/bin/phpunit -c test/phpunit.xml.dist
```

You'll notice some tests are skipped. The majority of the tests use mock Guzzle responses,
require no network connectivity, and don't touch the Akismet servers. If you wish to run
the "live" tests that connect to the API server, provide your API key in an environment
variable:

```sh
    export AKISMET_API_KEY="YOUR API KEY"
    php vendor/bin/phpunit -c test/phpunit.xml.dist
```


