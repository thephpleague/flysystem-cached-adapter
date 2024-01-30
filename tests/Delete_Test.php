<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\UnableToDeleteFile;

class Delete_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function file_is_not_cached_deleting(string $path): void
    {
        $this->cacheAdapter->delete($path);

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
        yield 'cache is purged after deleting' => ['fully-cached-file'];
        yield 'non cached file stays uncached' => ['non-cached-file'];
    }

    /** 
     * @test
     */
    public function cache_is_purged_after_unsuccessful_delete(): void
    {
        $path = 'deleted-cached-file';

        try {
            $this->cacheAdapter->delete($path);
        } catch (UnableToDeleteFile $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
