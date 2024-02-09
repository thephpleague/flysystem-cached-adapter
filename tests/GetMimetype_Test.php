<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\Visibility;
use Psr\Cache\InvalidArgumentException;

class GetMimetype_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function get_mimetype(string $path, StorageAttributes $expectedResult): void
    {
        $actualResult = $this->cacheAdapter->mimeType($path);

        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'fully cached file' => ['fully-cached-file.txt', new FileAttributes('fully-cached-file.txt', fileSize: 10, visibility: Visibility::PUBLIC, mimeType: 'text/plain')];
        yield 'partially cached file was updated' => ['partially-cached-file.txt', new FileAttributes('partially-cached-file.txt', fileSize: 10, visibility: Visibility::PUBLIC, mimeType: 'text/plain')];
        yield 'file is not cached but exists in the filesystem' => ['non-cached-file.txt', new FileAttributes('non-cached-file.txt', mimeType: 'text/plain')];
    }

    /** 
     * @test
     */
    public function file_is_cached_after_checking_filesystem(): void
    {
        $path = 'non-cached-file.txt';
        $this->cacheAdapter->mimeType($path);

        $this->assertCachedItems([
            $path => new FileAttributes($path, mimeType: 'text/plain'),
        ]);
    }

    /**
     * @test
     * @dataProvider errorDataProvider
     */
    public function error(string $path): void
    {
        $this->expectException(UnableToRetrieveMetadata::class);

        $this->cacheAdapter->mimeType($path);
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
            $this->cacheAdapter->mimeType($path);
        } catch (UnableToRetrieveMetadata $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
