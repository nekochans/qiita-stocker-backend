<?php
/**
 * Repository
 */

namespace App\Infrastructure\Repositories\Api;

use GuzzleHttp\Client;

/**
 * Class Repository
 * @package App\Infrastructure\Repositories\Api
 */
class Repository
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Repository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    final public function getClient(): Client
    {
        return $this->client;
    }
}
