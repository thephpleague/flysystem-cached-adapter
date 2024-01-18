# Flysystem Cache Adapter

![QA](https://github.com/jgivoni/flysystem-cache-adapter/actions/workflows/ci.yml/badge.svg)

## Changelog highlights
- **3.2**
  - [Support for psr/cache v2](https://github.com/jgivoni/flysystem-cache-adapter/issues/10)
- 3.1.2
  - [Bugfix when retrieving metadata on file which is a cached folder](https://github.com/jgivoni/flysystem-cache-adapter/issues/11)
- 3.1.1
  - [Development support for Symfony 7](https://github.com/jgivoni/flysystem-cache-adapter/issues/8)
- **3.1**
  - [Support for PHP 8.3](https://github.com/jgivoni/flysystem-cache-adapter/issues/6)

## Overwiew

This is the PSR-6 compliant cache adapter for **Flysystem v3** you're looking for!
The objective is to transparently cache file metadata and thereby improve performance when looking up whether a file exists, 
checking it's size or modification date etc.
It can be easily configured to work with in-memory cache, Redis, Memcached, Doctrine or the filesystem or any of the 
other [adapters available from Symfony](https://symfony.com/doc/current/components/cache.html#available-cache-adapters).

This library is not a direct fork of any other repository but written from scratch by me. I aim to keep it maintained, 
but I already consider it mature and there are no plans to add any new features.

## Why this library?

It supports **Flysystem v3** and is currently maintained, which makes it superior to these other alternatives that 
I was able to find.

- `league/flysystem-cached-adapter` (only supports Flysystem v1 which is no longer maintained)
- `lustmored/flysystem-v2-simple-cache-adapter` (doesn't appear to be maintained since January 2022)

In case you're wondering, the first version is called version 3 to match the Flysystem version it corresponds to.

## Installation

```bash
composer require jgivoni/flysystem-cache-adapter
```

## Example usage

```php
$cache = new \Symfony\Component\Cache\Adapter\RedisAdapter(...);

$filesystemAdapter = new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(...);

$cachedFilesystemAdapter = new \jgivoni\Flysystem\Cache\CacheAdapter($filesystemAdapter, $cache);
```
