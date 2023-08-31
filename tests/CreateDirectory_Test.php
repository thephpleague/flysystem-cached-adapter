<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;

class CreateDirectory_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function directory_is_cached_after_creating(string $path): void
    {
        $this->cacheAdapter->createDirectory($path, new Config);

        $this->assertCachedItems([
            $path => new DirectoryAttributes($path),
        ]);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'new directory' => ['non-existing-directory'];
        yield 'overwrite directory' => ['cached-directory'];
    }
}
