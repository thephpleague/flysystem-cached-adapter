<?php

namespace League\Flysystem\Cached\Storage;

use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;

abstract class AbstractCache implements CacheInterface
{
    /**
     * @var bool
     */
    protected $autosave = true;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var array
     */
    protected $complete = [];

    /**
     * @var Config
     */
    protected $config;

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if (! $this->autosave) {
            $this->save();
        }
    }

    /**
     * Get the config object or null.
     *
     * @return Config|null config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the config.
     *
     * @param Config|array|null $config
     */
    public function setConfig($config)
    {
        $this->config = Util::ensureConfig($config);
    }

    /**
     * Get the autosave setting.
     *
     * @return bool autosave
     */
    public function getAutosave()
    {
        return $this->autosave;
    }

    /**
     * Get the autosave setting.
     *
     * @param bool $autosave
     */
    public function setAutosave($autosave)
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
        $directories = [$directory];

        foreach ($contents as $object) {
            $this->updateObject($object['path'], $object);

            $object = $this->cache[$this->case($object['path'])];

            if ($recursive && $this->pathIsInDirectory($directory, $object['path'])) {
                $directories[] = $object['dirname'];
            }
        }

        foreach (array_unique($directories) as $directory) {
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
        $key = $this->case($path);

        if (! $this->has($path)) {
            $this->cache[$key] = Util::pathinfo($path);
        }

        $this->cache[$key] = array_merge($this->cache[$key], $object);

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
        $this->cache[$this->case($path)] = false;
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
        $key = $this->case($dirname);
        $result = [];

        foreach ($this->cache as $object) {
            if ($object === false) {
                continue;
            }

            if ($this->case($object['dirname']) === $key) {
                $result[] = $object;
            } elseif ($recursive && $this->pathIsInDirectory($key, $object['path'])) {
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
        $key = $this->case($path);

        if ($path !== false && array_key_exists($key, $this->cache)) {
            return $this->cache[$key] !== false;
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
        $key = $this->case($path);

        if (isset($this->cache[$key]['contents']) && $this->cache[$key]['contents'] !== false) {
            return $this->cache[$key];
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
        if (!$this->has($path)) {
            return;
        }

        $key = $this->case($path);
        $object = $this->cache[$key];

        unset($this->cache[$key]);

        $object['path'] = $newpath;
        $object = array_merge($object, Util::pathinfo($newpath));

        $this->cache[$this->case($newpath)] = $object;
        $this->autosave();
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        if (!$this->has($path)) {
            return;
        }

        $object = $this->cache[$this->case($path)];
        $object = array_merge($object, Util::pathinfo($newpath));

        $this->updateObject($newpath, $object, true);
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
        $dirname = $this->case($dirname);

        foreach ($this->cache as $path => $object) {
            $key = $this->case($path);

            if ($this->pathIsInDirectory($dirname, $path) || $key === $dirname) {
                unset($this->cache[$key]);
            }
        }

        unset($this->complete[$dirname]);

        $this->autosave();
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        $key = $this->case($path);

        if (isset($this->cache[$key]['mimetype'])) {
            return $this->cache[$key];
        }

        if (! $result = $this->read($path)) {
            return false;
        }

        $this->cache[$key]['mimetype'] = Util::guessMimeType($path, $result['contents']);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        $key = $this->case($path);

        if (isset($this->cache[$key]['size'])) {
            return $this->cache[$key];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        $key = $this->case($path);

        if (isset($this->cache[$key]['timestamp'])) {
            return $this->cache[$key];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        $key = $this->case($path);

        if (isset($this->cache[$key]['visibility'])) {
            return $this->cache[$key];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $key = $this->case($path);

        if (isset($this->cache[$key]['type'])) {
            return $this->cache[$key];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isComplete($dirname, $recursive)
    {
        $key = $this->case($dirname);

        if (! array_key_exists($key, $this->complete)) {
            return false;
        }

        if ($recursive && $this->complete[$key] !== 'recursive') {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setComplete($dirname, $recursive)
    {
        $this->complete[$this->case($dirname)] = $recursive ? 'recursive' : true;
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
        $cachedProperties = array_flip([
            'path', 'dirname', 'basename', 'extension', 'filename',
            'size', 'mimetype', 'visibility', 'timestamp', 'type',
            'md5',
        ]);

        foreach ($contents as $path => $object) {
            if (is_array($object)) {
                $contents[$this->case($path)] = array_intersect_key($object, $cachedProperties);
            }
        }

        return $contents;
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
        $object = $this->cache[$this->case($path)];

        while ($object['dirname'] !== '' && ! isset($this->cache[$this->case($object['dirname'])])) {
            $object = Util::pathinfo($object['dirname']);
            $object['type'] = 'dir';
            $this->cache[$this->case($object['path'])] = $object;
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
    protected function pathIsInDirectory($directory, $path)
    {
        return $directory === '' || strpos($this->case($path), $this->case($directory) . '/') === 0;
    }

    /**
     * Return the path string checking case_sensitive config value
     *
     * @param string $path
     *
     * @return string
     */
    protected function case($path)
    {
        if ($this->config && $this->config->get('case_sensitive', true) === false) {
            $path = strtolower($path);
        }

        return $path;
    }
}
