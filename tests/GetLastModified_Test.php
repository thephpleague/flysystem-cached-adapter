<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\UnableToRetrieveMetadata;

class GetLastModified_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function get_lastModified(string $path): void
    {
        $actualResult = $this->cacheAdapter->lastModified($path);

        self::assertNotNull($actualResult->lastModified());
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'partially cached file was updated' => ['partially-cached-file'];
    }

    /**
     * @test
     * @dataProvider errorDataProvider
     */
    public function error(string $path): void
    {
        $this->expectException(UnableToRetrieveMetadata::class);

        $this->cacheAdapter->lastModified($path);
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
            $this->cacheAdapter->lastModified($path);
        } catch (UnableToRetrieveMetadata $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
