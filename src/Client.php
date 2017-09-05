<?php
namespace Gothick\AkismetClient;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;

class Client
{

    /**
     * Akismet API key
     * 
     * @var string
     */
    private $api_key;

    private $guzzle_client;

    private $blog;

    private $app_name;

    private $app_version;
    
    const VERSION = '0.1';

    /**
     * Create an Akismet client.
     *
     * @param string $api_key
     */
    public function __construct($app_url, $app_name, $app_version, $api_key = null)
    {
        if (empty($app_url) || empty($app_name) || empty($app_version)) {
            throw new Exception('Must supply app URL, name and version in ' . __METHOD__);
        }
        // The Akismet API calls it a blog, so keep consistent.
        $this->blog = $app_url;
        
        $this->app_name = $app_name;
        $this->app_version = $app_version;
        $this->api_key = $api_key;
        // Well, there'd be no point in creating an instance of us unless we were going
        // to do some work, so we might as well warm up the Guzzle client here.
        
        // Add a bit of middleware to set our User-Agent header every time without
        // us having to worry about it in every method.
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        // TODO: Make sure this is working.
        $stack->push($this->add_header('User-Agent', $this->getOurUserAgent()));
        $this->guzzle_client = new \GuzzleHttp\Client();
    }

    private function getOurUserAgent()
    {
        // From the docs:
        // Setting your user agent If possible, your user agent string should always use the following format: Application Name/Version | Plugin Name/Version
        // e.g. WordPress/4.4.1 | Akismet/3.1.7
        // TODO: Check this is formatting correctly.
        // TODO: Add unit test
        return "{$this->app_name}/{$this->app_version} | Gothick\\AkismetClient/{self::VERSION}";
    }

    public function verifyKey($api_key = null)
    {
        $verified = false;
        $error = '';
        $key_to_verify = empty($api_key) ? $this->api_key : $api_key;
        
        $response = $this->guzzle_client->request('POST', $this->apiUri('verify-key'), [
            'form_params' => [
                "key" => $key_to_verify,
                "blog" => $this->blog
            ]
        ]);
        
        if ($response->getStatusCode() == 200) {
            $result = (string) $response->getBody();
            if ($result == 'valid') {
                $verified = true;
            } else {
                $error = "200 Response: $result";
            }
        } else {
            $error = (string) $response->getStatusCode();
            if ($response->hasHeader('X-akismet-debug-help')) {
                $error .= ': ' . $response->getHeader('X-akismet-debug-help');
            }
        }
        if (! $verified) {
            throw new \Exception(__METHOD__ . ': ' . $error);
        }
        return true;
    }

    /**
     * Check a comment for spam. See the Akismet API documentation for full details:
     * https://akismet.com/development/api/#comment-check. Returns a valid ClientResult
     * object or throws an exception.
     *
     * @param string $user_ip
     * @param string $user_agent
     * @param array $other_params
     *            See the Akismet API documentation for details
     * @param array $server_params
     *            This can just be $_SERVER, if you have access to it
     * @param string $user_role
     *            If 'administrator', will always pass the check
     * @param boolean $is_test
     *            Set to true for automated testing
     */
    public function commentCheck($user_ip, $user_agent, $other_params = array(), $server_params = array(), $user_role = 'user', $is_test = false)
    {
        if (empty($user_ip) || empty($user_agent)) {
            throw new Exception('Must provide user IP and user agent to ' . __METHOD__);
        }
        $params = array_merge($other_params, [
            'user_ip' => $user_ip,
            'user_agent' => $user_agent,
            'blog' => $this->blog
        ]);
        $params = array_merge($server_params, $params);
        $response = $this->guzzle_client->request('POST', $this->apiUri('comment-check'), [
            'form_params' => $params
        ]);

        $result = null;
        if ($response->getStatusCode() == 200) {
            $result = new ClientResult($response);
        } else {
            $error = (string) $response->getStatusCode();
            if ($response->hasHeader('X-akismet-debug-help')) {
                $error .= ': ' . $response->getHeader('X-akismet-debug-help');
            }
            throw new Exception('Unexpected status code in ' . __METHOD__ . ': ' . $error);
        }
        if ($result) {
            return $result;
        } else {
            throw new Exception('Unexpected error in ' . __METHOD__);
        }
    }

    private function apiUri($method)
    {
        if ($method == 'verify-key') {
            return "https://rest.akismet.com/1.1/verify-key";
        } else {
            if (empty($this->api_key)) {
                throw new Exception("Can't call authenticated method without setting an API key in " . __METHOD__);
            }
            return "https://{$this->api_key}.rest.akismet.com/1.1/$method";
        }
    }
    
    function add_header($header, $value)
    {
        return function (callable $handler) use ($header, $value) {
            return function (
                \Psr\Http\Message\RequestInterface $request,
                array $options
                ) use ($handler, $header, $value) {
                    $request = $request->withHeader($header, $value);
                    return $handler($request, $options);
            };
        };
    }
}