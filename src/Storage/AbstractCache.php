<?php

namespace League\Flysystem\Cached\Storage;

use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Util;

abstract class AbstractCache implements CacheInterface
{
    /**
     * @var bool
     */
    protected bool $autosave = true;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var array
     */
    protected $complete = [];


    public function __destruct()
    {
        if (! $this->autosave) {
            $this->save();
        }
    }

    public function getAutosave(): bool
    {
        return $this->autosave;
    }

    /**
     * Get the autosave setting.
     *
     * @param bool $autosave
     */
    public function setAutosave(bool $autosave): void
    {
        $this->autosave = $autosave;
    }


    /**
     * Store the contents listing.
     *
     * @param string $directory
     * @param array  $contents
     * @param bool   $recursive
     *
     * @return array contents listing
     */
    public function storeContents($directory, array $contents, $recursive = false)
    {
        // Use associative array instead of indexed array
        $directories = [$directory => true];

        foreach ($contents as $object) {
            $this->updateObject($object['path'], $object);

            // Assign it to a variable before the loop
            $objectCachePath = $this->cache[$object['path']];

            if ($recursive && $this->pathIsInDirectory($directory, $objectCachePath['path'])) {
                // Check if the directory is already present using isset()
                if (!isset($directories[$objectCachePath['dirname']])) {
                    $directories[$objectCachePath['dirname']] = true;
                }
            }
        }

        // Since $directories is now an associative array, use array_keys() to get the keys
        foreach (array_keys($directories) as $directory) {
            $this->setComplete($directory, $recursive);
        }

        $this->autosave();
    }


    /**
     * Update the metadata for an object.
     *
     * @param string $path     object path
     * @param array  $object   object metadata
     * @param bool   $autosave whether to trigger the autosave routine
     */
    public function updateObject($path, array $object, $autosave = false)
    {
        if (! $this->has($path)) {
            $this->cache[$path] = Util::pathinfo($path) + $object;
        } else {
            $this->cache[$path] += $object;
        }

        if ($autosave) {
            $this->autosave();
        }

        $this->ensureParentDirectories($path);
    }


    /**
     * Store object hit miss.
     *
     * @param string $path
     */
    public function storeMiss($path)
    {
        $this->cache[$path] = false;
        $this->autosave();
    }

    /**
     * Get the contents listing.
     *
     * @param string $dirname
     * @param bool   $recursive
     *
     * @return array contents listing
     */
    public function listContents($dirname = '', $recursive = false)
    {
        $result = [];

        foreach ($this->cache as $object) {
            if ($object === false) {
                continue;
            }

            $inDirectory = $object['dirname'] === $dirname || ($recursive && $this->pathIsInDirectory($dirname, $object['path']));

            if ($inDirectory) {
                $result[] = $object;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        if ($path !== false && array_key_exists($path, $this->cache)) {
            return $this->cache[$path] !== false;
        }

        if ($this->isComplete(Util::dirname($path), false)) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        if (isset($this->cache[$path]['contents']) && $this->cache[$path]['contents'] !== false) {
            return $this->cache[$path];
        }

        return false;
    }



    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        if ($this->has($path)) {
            $object = $this->cache[$path];
            unset($this->cache[$path]);
            $object['path'] = $newpath;
            $object = array_merge($object, Util::pathinfo($newpath));
            $this->cache[$newpath] = $object;
            $this->autosave();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        if ($this->has($path)) {
            $object = $this->cache[$path];
            $object = array_merge($object, Util::pathinfo($newpath));
            $this->updateObject($newpath, $object, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $this->storeMiss($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        // We filter out any elements of the cache that belong to the directory
        // we are deleting, and assign the filtered array back to $this->cache.
        $this->cache = array_filter(
            $this->cache,
            function($path) use ($dirname) {
                return !($this->pathIsInDirectory($dirname, $path) || $path === $dirname);
            },
            ARRAY_FILTER_USE_KEY
        );

        unset($this->complete[$dirname]);

        $this->autosave();
    }


    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        if (isset($this->cache[$path]['mimetype'])) {
            return $this->cache[$path];
        }

        if (! $result = $this->read($path)) {
            return false;
        }

        $mimetype = Util::guessMimeType($path, $result['contents']);
        $this->cache[$path]['mimetype'] = $mimetype;

        return $this->cache[$path];
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        if (isset($this->cache[$path]['size'])) {
            return $this->cache[$path];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        if (isset($this->cache[$path]['timestamp'])) {
            return $this->cache[$path];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        if (isset($this->cache[$path]['visibility'])) {
            return $this->cache[$path];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        if (isset($this->cache[$path]['type'])) {
            return $this->cache[$path];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isComplete($dirname, $recursive)
    {
        return array_key_exists($dirname, $this->complete)
            && (!$recursive || $this->complete[$dirname] === 'recursive');
    }


    /**
     * {@inheritdoc}
     */
    public function setComplete($dirname, $recursive)
    {
        $this->complete[$dirname] = $recursive ? 'recursive' : true;
    }

    /**
     * Filter the contents from a listing.
     *
     * @param array $contents object listing
     *
     * @return array filtered contents
     */
    public function cleanContents(array $contents)
    {
        // Defined properties array that needs to be cached
        $properties = [
            'path', 'dirname', 'basename', 'extension', 'filename',
            'size', 'mimetype', 'visibility', 'timestamp', 'type',
            'md5',
        ];

        // Flipped the properties array once outside of the loop to avoid repeated computation
        $cachedProperties = array_flip($properties);

        // Using array_map instead of foreach for better readability and performance.
        // array_map applies a callback to each element in the array and returns a new array with the results
        return array_map(function($object) use ($cachedProperties) {
            if (is_array($object)) {
                // Intersect the keys of the object with the cached properties
                return array_intersect_key($object, $cachedProperties);
            }
            return $object;
        }, $contents);
    }


    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->cache = [];
        $this->complete = [];
        $this->autosave();
    }

    /**
     * {@inheritdoc}
     */
    public function autosave()
    {
        if ($this->autosave) {
            $this->save();
        }
    }

    /**
     * Retrieve serialized cache data.
     *
     * @return string serialized data
     */
    public function getForStorage()
    {
        $cleaned = $this->cleanContents($this->cache);

        return json_encode([$cleaned, $this->complete]);
    }

    /**
     * Load from serialized cache data.
     *
     * @param string $json
     */
    public function setFromStorage($json)
    {
        list($cache, $complete) = json_decode($json, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($cache) && is_array($complete)) {
            $this->cache = $cache;
            $this->complete = $complete;
        }
    }

    /**
     * Ensure parent directories of an object.
     *
     * @param string $path object path
     */
    public function ensureParentDirectories($path)
    {
        // Getting the initial object from cache
        $object = $this->cache[$path];

        // We'll keep creating parent directories until the dirname is empty or the directory already exists
        while ($object['dirname'] !== '' && ! isset($this->cache[$object['dirname']])) {
            $object = Util::pathinfo($object['dirname']); // updating the object to be the parent directory
            $object['type'] = 'dir'; // it's a directory, so we'll set the type to 'dir'

            // The new object represents the parent directory, so we'll use the 'path' as the key
            $this->cache[$object['path']] = $object;
        }
    }


    /**
     * Determines if the path is inside the directory.
     *
     * @param string $directory
     * @param string $path
     *
     * @return bool
     */
    protected function pathIsInDirectory(string $directory, string $path): bool
    {
        return $directory === '' || str_starts_with($path, $directory . '/');
    }

}
