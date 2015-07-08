<?php

namespace League\Flysystem\Cached\Storage;

use Redis;

class PhpRedis extends AbstractCache
{
    /**
     * @var \Redis PhpRedis Client
     */
    protected $client;

    /**
     * @var string storage key
     */
    protected $key;

    /**
     * @var int|null seconds until cache expiration
     */
    protected $expire;

    /**
     * Constructor.
     *
     * @param \Redis $client phpredis client
     * @param string         $key    storage key
     * @param int|null       $expire seconds until cache expiration
     */
    public function __construct(Redis $client = null, $key = 'flysystem', $expire = null)
    {
        $this->client = $client ?: new Redis();
        $this->key = $key;
        $this->expire = $expire;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        if (($contents = $this->client->get($this->key)) !== false) {
            $this->setFromStorage($contents);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $contents = $this->getForStorage();
        $this->client->set($this->key, $contents);

        if ($this->expire !== null) {
            $this->client->expire($this->key, $this->expire);
        }
    }
}
