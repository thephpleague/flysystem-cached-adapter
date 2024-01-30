<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\Visibility;

class SetVisibility_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function set_visibility_ok(string $path, string $visibility, StorageAttributes $expectedStorageAttributes): void
    {
        $this->cacheAdapter->setVisibility($path, $visibility);

        $this->assertCachedItems([
            $path => $expectedStorageAttributes,
        ]);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'cached file' => ['fully-cached-file', Visibility::PUBLIC, new FileAttributes('fully-cached-file', fileSize: 10, visibility: Visibility::PUBLIC)];
        yield 'cached file, set private' => ['fully-cached-file', Visibility::PRIVATE, new FileAttributes('fully-cached-file', fileSize: 10, visibility: Visibility::PRIVATE)];

        // Cannot test setting visibility on directory with in-memory adapter - only files
        // yield 'cached directory' => ['cached-directory', Visibility::PUBLIC, new DirectoryAttributes('fully-cached-file', visibility: Visibility::PUBLIC)];
        // yield 'cached directory, set private' => ['cached-directory', Visibility::PRIVATE, new DirectoryAttributes('fully-cached-file', visibility: Visibility::PRIVATE)];
    }

    /** 
     * @test
     */
    public function cache_is_purged_after_unsuccessful_set(): void
    {
        $path = 'deleted-cached-file';

        try {
            $this->cacheAdapter->setVisibility($path, Visibility::PRIVATE);
        } catch (UnableToSetVisibility $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
