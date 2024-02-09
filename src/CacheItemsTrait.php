<?php

namespace jgivoni\Flysystem\Cache;

use Closure;
use League\Flysystem\StorageAttributes;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToProvideChecksum;
use League\Flysystem\UnableToRetrieveMetadata;
use RuntimeException;

/**
 * Trait for handling cache items in CacheAdapter
 *
 * @property FilesystemAdapter $adapter
 * @property CacheItemPoolInterface $cache
 */
trait CacheItemsTrait
{
    static string $CACHE_KEY_PREFIX = 'flysystem_item_';
    static string $CACHE_KEY_HASH_SALT = '563ce5132194441b';

    protected function getCacheItem(string $path): CacheItemInterface
    {
        $key = self::getCacheItemKey($path);

        return $this->cache->getItem($key);
    }

    protected function saveCacheItem(CacheItemInterface $cacheItem): void
    {
        $this->cache->save($cacheItem);
    }

    protected function deleteCacheItem(CacheItemInterface $cacheItem): void
    {
        $this->cache->deleteItem($cacheItem->getKey());
    }

    protected function purgeCacheItem(string $path): void
    {
        $item = $this->getCacheItem($path);
        if ($item->isHit()) {
            $this->deleteCacheItem($item);
        }
    }

    public static function getCacheItemKey(string $path): string
    {
        return self::$CACHE_KEY_PREFIX . md5(self::$CACHE_KEY_HASH_SALT . $path);
    }

    protected function addCacheEntry(string $path, StorageAttributes $storageAttributes): void
    {
        $item = $this->getCacheItem($path);

        $item->set($storageAttributes);

        $this->saveCacheItem($item);
    }

    /**
     * Returns a new FileAttributes with all properties from $fileAttributesExtension
     * overriding existing properties from $fileAttributesBase (with the exception of path)
     *
     * For extraMetadata, each individual element in the array is also merged
     */
    protected static function mergeFileAttributes(
        FileAttributes $fileAttributesBase,
        FileAttributes $fileAttributesExtension
    ): FileAttributes {
        return new FileAttributes(
            path: $fileAttributesBase->path(),
            fileSize: $fileAttributesExtension->fileSize() ??
                $fileAttributesBase->fileSize(),
            visibility: $fileAttributesExtension->visibility() ??
                $fileAttributesBase->visibility(),
            lastModified: $fileAttributesExtension->lastModified() ??
                $fileAttributesBase->lastModified(),
            mimeType: $fileAttributesExtension->mimeType() ??
                $fileAttributesBase->mimeType(),
            extraMetadata: array_merge(
                $fileAttributesBase->extraMetadata(),
                $fileAttributesExtension->extraMetadata()
            ),
        );
    }

    /**
     * Returns a new DirectoryAttributes with all properties from $directoryAttributesExtension
     * overriding existing properties from $directoryAttributesBase (with the exception of path)
     *
     * For extraMetadata, each individual element in the array is also merged
     */
    protected static function mergeDirectoryAttributes(
        DirectoryAttributes $directoryAttributesBase,
        DirectoryAttributes $directoryAttributesExtension
    ): DirectoryAttributes {
        return new DirectoryAttributes(
            path: $directoryAttributesBase->path(),
            visibility: $directoryAttributesExtension->visibility() ??
                $directoryAttributesBase->visibility(),
            lastModified: $directoryAttributesExtension->lastModified() ??
                $directoryAttributesBase->lastModified(),
            extraMetadata: array_merge(
                $directoryAttributesBase->extraMetadata(),
                $directoryAttributesExtension->extraMetadata()
            ),
        );
    }

    /**
     * Returns FileAttributes from cache if desired attribute is found,
     * or loads the desired missing attribute from the adapter and merges it with the cached attributes.
     *
     * @param Closure $loader Returns FileAttributes with the desired attribute loaded from adapter
     * @param Closure $attributeAccessor Returns value of desired attribute from cached item
     */
    protected function getFileAttributes(
        string $path,
        Closure $loader,
        Closure $attributeAccessor,
    ): FileAttributes {
        $item = $this->getCacheItem($path);

        if ($item->isHit()) {
            /** @var FileAttributes $fileAttributes */
            $fileAttributes = $item->get();

            if (!$fileAttributes instanceof FileAttributes) {
                throw new RuntimeException('Cached item is not a file');
            }
        } else {
            $fileAttributes = new FileAttributes(
                path: $path,
            );
        }

        if ($attributeAccessor($fileAttributes) === null) {
            try {
                $fileAttributesExtension = $loader();
            } catch (UnableToRetrieveMetadata | UnableToProvideChecksum $e) {
                $this->purgeCacheItem($path);
                throw $e;
            }

            $fileAttributes = self::mergeFileAttributes(
                fileAttributesBase: $fileAttributes,
                fileAttributesExtension: $fileAttributesExtension,
            );

            $item->set($fileAttributes);

            $this->saveCacheItem($item);
        }

        return $fileAttributes;
    }
}
