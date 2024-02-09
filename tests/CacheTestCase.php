<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use jgivoni\Flysystem\Cache\CacheAdapter;
use League\Flysystem\Config;
use League\Flysystem\StorageAttributes;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\Visibility;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheTestCase extends TestCase
{
    protected CacheAdapter $cacheAdapter;

    protected ArrayAdapter $cachePool;

    protected InMemoryFilesystemAdapter $fileSystemAdapter;

    public function setUp(): void
    {
        parent::setUp();

        $this->cachePool = new ArrayAdapter();

        $this->fileSystemAdapter = new InMemoryFilesystemAdapter();

        $this->cacheAdapter = new CacheAdapter($this->fileSystemAdapter, $this->cachePool);

        $this->setupCache([
            'fully-cached-file' => new FileAttributes('fully-cached-file', 10, Visibility::PUBLIC),
            'partially-cached-file' => new FileAttributes('partially-cached-file'),
            'deleted-cached-file' => new FileAttributes('deleted-cached-file', 10, Visibility::PUBLIC),
            'partially-cached-deleted-file' => new FileAttributes('partially-cached-deleted-file'),
            'overwritten-file' => new FileAttributes('overwritten-file', 20, Visibility::PUBLIC),
            'cached-directory' => new DirectoryAttributes('cached-directory', visibility: Visibility::PUBLIC),
            'cached-directory/file' => new FileAttributes('cached-directory/file', 10),
            'deleted-cached-directory' => new DirectoryAttributes('deleted-cached-directory'),
            'fully-cached-file.txt' => new FileAttributes('fully-cached-file.txt', 10, Visibility::PUBLIC, mimeType: 'text/plain'),
            'partially-cached-file.txt' => new FileAttributes('partially-cached-file.txt', 10, Visibility::PUBLIC),
        ]);

        $this->setupFiles([
            'fully-cached-file' => '0123456789',
            'partially-cached-file' => '0123456789',
            'overwritten-file' => '0123456789',
            'non-cached-file' => '0123456789',
            'cached-directory/file' => '0123456789',
            'non-cached-directory/file' => '0123456789',
            'fully-cached-file.txt' => '0123456789',
            'partially-cached-file.txt' => '0123456789',
            'non-cached-file.txt' => '0123456789',
        ]);
    }

    /**
     * @param array<string, FileAttributes|DirectoryAttributes> $items 
     */
    protected function setupCache(array $items): void
    {
        foreach ($items as $path => $storageAttributes) {
            $item = $this->cachePool->getItem(CacheAdapter::getCacheItemKey($path));

            $item->set($storageAttributes);

            $this->cachePool->save($item);
        }
    }

    /**
     * @param array<string, string> $items 
     */
    protected function setupFiles(array $items): void
    {
        foreach ($items as $path => $contents) {
            $this->fileSystemAdapter->write($path, $contents, new Config(['timestamp' => \strtotime('2023-01-01 12:00:00')]));
        }
    }

    /**
     * @param array<string, StorageAttributes|FileAttributes|DirectoryAttributes|null> $items 
     */
    protected function assertCachedItems(array $items): void
    {
        foreach ($items as $path => $expectedStorageAttributes) {
            $item = $this->cachePool->getItem(CacheAdapter::getCacheItemKey($path));
            $cacheStorageAttributes = $item->get();

            self::assertEquals($expectedStorageAttributes, $cacheStorageAttributes);
        }
    }
}
