<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\Visibility;

class GetVisibility_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function get_visibility(string $path, StorageAttributes $expectedResult): void
    {
        $actualResult = $this->cacheAdapter->visibility($path);

        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'cached file' => ['fully-cached-file', new FileAttributes('fully-cached-file', fileSize: 10, visibility: Visibility::PUBLIC)];
        yield 'missing file was cached' => ['deleted-cached-file', new FileAttributes('deleted-cached-file', fileSize: 10, visibility: Visibility::PUBLIC)];
        yield 'file is not cached but exists in the filesystem' => ['non-cached-file', new FileAttributes('non-cached-file', visibility: Visibility::PUBLIC)];
    }

    /** 
     * @test
     */
    public function file_is_cached_after_checking_filesystem(): void
    {
        $path = 'non-cached-file';
        $this->cacheAdapter->visibility($path);

        $this->assertCachedItems([
            $path => new FileAttributes($path, visibility: Visibility::PUBLIC),
        ]);
    }

    /**
     * @test
     * @dataProvider errorDataProvider
     */
    public function error(string $path): void
    {
        $this->expectException(UnableToRetrieveMetadata::class);

        $this->cacheAdapter->visibility($path);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function errorDataProvider(): iterable
    {
        yield 'File not found' => ['nonexistingfile'];
        yield 'Path is directory (cached)' => ['cached-directory'];
        yield 'Path is directory (non-cached)' => ['non-cached-directory'];
    }

    /** 
     * @test
     */
    public function cache_is_purged_after_unsuccessful_get(): void
    {
        $path = 'partially-cached-deleted-file';

        try {
            $this->cacheAdapter->visibility($path);
        } catch (UnableToRetrieveMetadata $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
