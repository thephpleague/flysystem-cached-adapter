<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;
use League\Flysystem\Visibility;

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
}
