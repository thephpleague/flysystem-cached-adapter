<?php

namespace tests\jgivoni\Flysystem\Cache;

use jgivoni\Flysystem\Cache\CacheItemsTrait;
use League\Flysystem\FileAttributes;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheItemsTrait_Test extends TestCase
{
    /**
     * @test
     */
    public function return_hit_after_miss(): void
    {
        $adapter = new class {
            use CacheItemsTrait;

            public function __construct(
                protected readonly CacheItemPoolInterface $cache = new ArrayAdapter()
            ) {
            }

            public function getItem(string $path): CacheItemInterface
            {
                return $this->getCacheItem($path);
            }

            public function saveItem(CacheItemInterface $item): void
            {
                $this->saveCacheItem($item);
            }
        };

        $path = 'test.txt';
        $item = $adapter->getItem($path);

        self::assertFalse($item->isHit());

        $item->set(new FileAttributes(
            path: $path
        ));

        $adapter->saveItem($item);

        $freshItem = $adapter->getItem($path);
        self::assertTrue($freshItem->isHit());
    }
}
