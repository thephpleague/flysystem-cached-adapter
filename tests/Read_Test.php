<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\Visibility;

class Read_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function file_is_cached_after_reading(string $path, int|null $expectedSize = \null, string|null $expectedVisibility = \null): void
    {
        $this->cacheAdapter->read($path);

        $this->assertCachedItems([
            $path => new FileAttributes($path, $expectedSize, $expectedVisibility),
        ]);
    }

    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function file_is_cached_after_reading_stream(string $path, int|null $expectedSize = \null, string|null $expectedVisibility = \null): void
    {
        $this->cacheAdapter->readStream($path);

        $this->assertCachedItems([
            $path => new FileAttributes($path, $expectedSize, $expectedVisibility),
        ]);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'file is cached after reading' => ['non-cached-file'];
        yield 'file stays cached after reading' => ['partially-cached-file'];
        yield 'cached file attributes are unchanged after reading' => ['fully-cached-file', 10, Visibility::PUBLIC];
    }

    /** 
     * @test
     */
    public function cache_is_purged_after_unsuccessful_read(): void
    {
        $path = 'deleted-cached-file';

        try {
            $this->cacheAdapter->read($path);
        } catch (UnableToReadFile $e) {
            $this->assertCachedItems([
                $path => \null,
            ]);
        }
    }

    /** 
     * @test
     */
    public function cache_is_purged_after_unsuccessful_readStream(): void
    {
        $path = 'deleted-cached-file';

        try {
            $this->cacheAdapter->readStream($path);
        } catch (UnableToReadFile $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
