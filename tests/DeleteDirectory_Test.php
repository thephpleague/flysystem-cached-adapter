<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\UnableToDeleteDirectory;

class DeleteDirectory_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function directory_is_not_cached_deleting(string $path): void
    {
        $this->cacheAdapter->deleteDirectory($path);

        $this->assertCachedItems([
            $path => \null,
        ]);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'cache is purged after deleting' => ['cached-directory'];
        yield 'non cached directory stays uncached' => ['non-cached-directory'];
    }

    /**
     * @test
     */
    public function nested_files_are_purged_from_cache(): void
    {
        $this->cacheAdapter->deleteDirectory('cached-directory');

        $this->assertCachedItems([
            'cached-directory/file' => \null,
        ]);
    }

    /** 
     * @test
     */
    public function cache_is_purged_after_unsuccessful_delete(): void
    {
        $path = 'deleted-cached-directory';

        try {
            $this->cacheAdapter->deleteDirectory($path);
        } catch (UnableToDeleteDirectory $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
