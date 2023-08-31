<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\Visibility;

class Move_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function move_ok(string $source, FileAttributes $expectedDestinationCacheItem): void
    {
        $destination = 'destination';

        $this->cacheAdapter->move($source, $destination, new Config);

        $this->assertCachedItems([
            $source => \null,
            $destination => $expectedDestinationCacheItem,
        ]);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'cache item is moved' => ['fully-cached-file', new FileAttributes('destination', 10, Visibility::PUBLIC)];
        yield 'cache item is created' => ['non-cached-file', new FileAttributes('destination')];
    }
}
