# Flysystem Cached Adapter

The adapter decorator caches metadata and directory listings.

## Usage

```php
<?php

use League\Flysystem\Cache\Memcached;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\Adapter;

$adapter = new Adapter(new Local(__DIR__.'/something/'), new Memcached($memcachedInstance));

```