<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\DirectoryAttributes;

class DirectoryExists_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function directory_exists_ok(string $path, bool $expectedResult): void
    {
        $actualResult = $this->cacheAdapter->directoryExists($path);

        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'directory is cached' => ['cached-directory', true];
        yield 'missing directory was cached and still returns true' => ['deleted-cached-directory', true];
        yield 'directory is not cached but exists in the filesystem' => ['non-cached-directory', true];
        yield 'directory does not exist' => ['non-existing-directory', false];
        yield 'file is not a directory' => ['fully-cached-file', false];
    }

    /** 
     * @test
     */
    public function directory_is_cached_after_checking_filesystem(): void
    {
        $path = 'non-cached-directory';
        $this->cacheAdapter->directoryExists($path);

        $this->assertCachedItems([
            $path => new DirectoryAttributes($path),
        ]);
    }
}
