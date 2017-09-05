<?php 
namespace Gothick\AkismetClient;

class ClientResult
{
    const PRO_TIP_HEADER = 'X-akismet-pro-tip';
    private $raw_result;
    private $pro_tip;
    
    public function __construct(\GuzzleHttp\Psr7\Response $response)
    {
        if ($response->getStatusCode() != 200)
        {
            // Our clients are meant to check first
            throw new Exception('Response with invalid status code in ' . __METHOD__);
        }
        $this->raw_result = (string) $response->getBody();
        if ($response->hasHeader(self::PRO_TIP_HEADER))
        {
            $this->pro_tip = $response->getHeader(self::PRO_TIP_HEADER);
        }
    }
    
    public function isSpam() {
        return $this->raw_result == 'true';
    }
    public function isBlatantSpam() {
        return (isSpam() && $this->pro_tip == 'disacrd');
    }
}