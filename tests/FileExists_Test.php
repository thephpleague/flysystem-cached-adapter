<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\FileAttributes;

class FileExists_Test extends CacheTestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function file_exists_ok(string $path, bool $expectedResult): void
    {
        $actualResult = $this->cacheAdapter->fileExists($path);

        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     *
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'cached file returns true' => ['fully-cached-file', true];
        yield 'missing file was cached and still returns true' => ['deleted-cached-file', true];
        yield 'file is not cached but exists in the filesystem' => ['non-cached-file', true];
        yield 'file does not exist' => ['non-existing-file', false];
        yield 'directory is not a file' => ['cached-directory', false];
    }

    /**
     * @test
     */
    public function file_is_cached_after_checking_filesystem(): void
    {
        $path = 'non-cached-file';
        $this->cacheAdapter->fileExists($path);

        $this->assertCachedItems([
            $path => new FileAttributes($path),
        ]);
    }
}
