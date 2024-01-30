<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\Visibility;

class Copy_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function copy_ok(string $source, FileAttributes|null $expectedSourceCacheItem, FileAttributes $expectedDestinationCacheItem): void
    {
        $destination = 'destination';

        $this->cacheAdapter->copy($source, $destination, new Config);

        $this->assertCachedItems([
            $source => $expectedSourceCacheItem,
            $destination => $expectedDestinationCacheItem,
        ]);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'cache item is copied' => ['fully-cached-file', new FileAttributes('fully-cached-file', 10, Visibility::PUBLIC), new FileAttributes('destination', 10, Visibility::PUBLIC)];
        yield 'cache item is created' => ['non-cached-file', \null, new FileAttributes('destination')];
    }

    /** 
     * @test
     */
    public function cache_is_purged_after_unsuccessful_copy(): void
    {
        $path = 'deleted-cached-file';

        try {
            $this->cacheAdapter->copy($path, 'destination', new Config);
        } catch (UnableToCopyFile $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
