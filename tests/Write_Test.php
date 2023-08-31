<?php

namespace tests\jgivoni\Flysystem\Cache;

use ErrorException;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;

class Write_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function file_is_cached_after_writing(string $path): void
    {
        $this->cacheAdapter->write($path, '0123456789', new Config);

        $this->assertCachedItems([
            $path => new FileAttributes($path),
        ]);
    }

    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function file_is_cached_after_writing_stream(string $path): void
    {
        $resource = fopen('php://memory', 'w+') ?: throw new ErrorException('Could not open stream');

        $this->cacheAdapter->writeStream($path, $resource, new Config);

        $this->assertCachedItems([
            $path => new FileAttributes($path),
        ]);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'new file' => ['non-existing-file'];
        yield 'overwrite file, cached attributes are removed' => ['fully-cached-file'];
    }
}
