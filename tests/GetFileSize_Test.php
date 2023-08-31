<?php

namespace tests\jgivoni\Flysystem\Cache;

class GetFileSize_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function get_fileSize(string $path, int $expectedFileSize): void
    {
        $actualResult = $this->cacheAdapter->fileSize($path);

        self::assertEquals($expectedFileSize, $actualResult->fileSize());
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'fully cached file' => ['fully-cached-file', 10];
        yield 'partially cached file reads from filesystem' => ['partially-cached-file', 10];
        yield 'cached file returns last known file size' => ['deleted-cached-file', 10];
        yield 'overwritten file still returns old file size' => ['overwritten-file', 20];
    }
}
