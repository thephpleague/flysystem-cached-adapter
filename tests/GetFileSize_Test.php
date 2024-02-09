<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\UnableToRetrieveMetadata;

class GetFileSize_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function get_fileSize(string $path, int $expectedFileSize): void
    {
        $actualResult = $this->cacheAdapter->fileSize($path);

        self::assertEquals($expectedFileSize, $actualResult->fileSize());
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'fully cached file' => ['fully-cached-file', 10];
        yield 'partially cached file reads from filesystem' => ['partially-cached-file', 10];
        yield 'cached file returns last known file size' => ['deleted-cached-file', 10];
        yield 'overwritten file still returns old file size' => ['overwritten-file', 20];
    }

    /**
     * @test
     * @dataProvider errorDataProvider
     */
    public function error(string $path): void
    {
        $this->expectException(UnableToRetrieveMetadata::class);

        $this->cacheAdapter->fileSize($path);
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
            $this->cacheAdapter->fileSize($path);
        } catch (UnableToRetrieveMetadata $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
