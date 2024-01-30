<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToMoveFile;
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

    /** 
     * @test
     */
    public function cache_is_purged_after_unsuccessful_move(): void
    {
        $path = 'deleted-cached-file';

        try {
            $this->cacheAdapter->move($path, 'destination', new Config);
        } catch (UnableToMoveFile $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
