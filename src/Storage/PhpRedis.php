<?php

declare(strict_types=1);

namespace League\Flysystem\Cached\Storage;

use Redis;

class PhpRedis extends AbstractCache
{
    protected Redis $client;
    protected string $key;
    protected ?int $ttl;

    public function __construct(?Redis $client = null, string $key = 'flysystem', ?int $ttl = null)
    {
        $this->client = $client ?? new Redis();
        $this->key = $key;
        $this->ttl = $ttl;
    }

    public function load(): void
    {
        $contents = $this->client->get($this->key);

        if ($contents !== false) {
            $this->setFromStorage($contents);
        }
    }

    public function save(): void
    {
        $contents = $this->getForStorage();
        $this->client->set($this->key, $contents);

        if ($this->ttl !== null) {
            $this->client->expire($this->key, $this->ttl);
        }
    }
}
