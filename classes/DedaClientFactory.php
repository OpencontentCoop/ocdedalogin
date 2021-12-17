<?php

use Symfony\Component\HttpClient\HttpClient;

class DedaClientFactory
{
    private static $instance;

    private $client;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (self::$instance === null){
            self::$instance = new DedaClientFactory();
        }

        return self::$instance;
    }

    /**
     * @return DedaClient
     */
    public function makeClient()
    {
        if ($this->client === null) {
            $client = HttpClient::create();
            $settings = eZINI::instance('dedalogin.ini')->group('Settings');
            $this->client = new DedaClient($client, $settings['BaseUrl'], $settings['ClientId'], $settings['Secret'], $settings['Issuer']);
        }

        return $this->client;
    }
}
