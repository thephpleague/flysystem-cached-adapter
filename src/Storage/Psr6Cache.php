<?php

declare(strict_types=1);

namespace League\Flysystem\Cached\Storage;

use Psr\Cache\CacheItemPoolInterface;

class Psr6Cache extends AbstractCache
{
    private CacheItemPoolInterface $pool;
    protected string $key;
    protected ?int $ttl;

    public function __construct(CacheItemPoolInterface $pool, string $key = 'flysystem', ?int $ttl = null)
    {
        $this->pool = $pool;
        $this->key = $key;
        $this->ttl = $ttl;
    }

    public function save(): void
    {
        $item = $this->pool->getItem($this->key);
        $item->set($this->getForStorage());
        $item->expiresAfter($this->ttl);
        $this->pool->save($item);
    }

    public function load(): void
    {
        $item = $this->pool->getItem($this->key);
        if ($item->isHit()) {
            $this->setFromStorage($item->get());
        }
    }
}
