# Flysystem Cache Adapter

![QA](https://github.com/jgivoni/flysystem-cache-adapter/actions/workflows/ci.yml/badge.svg)

A PSR-6 compliant adapter decorator for Flysystem v3 that caches file metadata to improve performance.

## Why this library?

It supports Flysystem v3 and is currently maintained, which makes it superior to existing alternatives:

- `league/flysystem-cached-adapter` (only supports Flysystem v1 which is no longer maintained)
- `lustmored/flysystem-v2-simple-cache-adapter` (doesn't appear to be maintained since January 2022)

## Installation

```bash
composer require jgivoni/flysystem-cache-adapter
```

## Example usage

```php
$cache = new \Symfony\Component\Cache\Adapter\RedisAdapter(...);

$filesystemAdapter = new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(...);

$cachedFilesystemAdapter = new \jgivoni\Flysysten\Cache\CacheAdapter($filesystemAdapter, $cache);
```
