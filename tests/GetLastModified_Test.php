<?php

namespace tests\jgivoni\Flysystem\Cache;

class GetLastModified_Test extends CacheTestCase
{
    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function get_lastModified(string $path): void
    {
        $actualResult = $this->cacheAdapter->lastModified($path);

        self::assertNotNull($actualResult->lastModified());
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'partially cached file was updated' => ['partially-cached-file'];
    }
}
