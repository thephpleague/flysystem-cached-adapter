<?php

namespace League\Flysystem\Cached\Storage;

use Memcached as NativeMemcached;

class Memcached extends AbstractCache
{
    /**
     * @var string storage key
     */
    protected $key;

    /**
     * @var int|null seconds until cache expiration
     */
    protected $expire;

    /**
     * @var \Memcached Memcached instance
     */
    protected $memcached;

    /**
     * Constructor.
     *
     * @param \Memcached        $memcached
     * @param string            $key       storage key
     * @param int|null          $expire    seconds until cache expiration
     * @param Config|array|null $config    settings values
     */
    public function __construct(NativeMemcached $memcached, $key = 'flysystem', $expire = null, $config = [])
    {
        $this->key = $key;
        $this->expire = $expire;
        $this->memcached = $memcached;
        $this->setConfig($config);
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $contents = $this->memcached->get($this->key);

        if ($contents !== false) {
            $this->setFromStorage($contents);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $contents = $this->getForStorage();
        $expiration = $this->expire === null ? 0 : time() + $this->expire;
        $this->memcached->set($this->key, $contents, $expiration);
    }
}
