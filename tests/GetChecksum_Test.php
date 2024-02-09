<?php

namespace tests\jgivoni\Flysystem\Cache;

use jgivoni\Flysystem\Cache\CacheAdapter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToProvideChecksum;
use League\Flysystem\UnableToRetrieveMetadata;
use Mockery;
use Mockery\MockInterface;

class GetChecksum_Test extends CacheTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setupCache([
            'fully-cached-file-with-checksum' => new FileAttributes('fully-cached-file-with-checksum', extraMetadata: ['checksum' => 'my-cached-checksum']),
            'partially-cached-file-with-aws-etag' => new FileAttributes('partially-cached-file-with-aws-etag', extraMetadata: ['ETag' => '"my-cached-aws-etag"']),
        ]);
    }

    /** 
     * @test
     * @dataProvider dataProvider
     */
    public function get_checksum(string $path, string $expectedChecksum): void
    {
        $actualResult = $this->cacheAdapter->checksum($path, new Config);

        self::assertEquals($expectedChecksum, $actualResult);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function dataProvider(): iterable
    {
        yield 'checksum is cached' => ['fully-cached-file-with-checksum', 'my-cached-checksum'];
        yield 'checksum is not cached' => ['partially-cached-file', md5('0123456789')];
        yield 'file is not cached' => ['non-cached-file', md5('0123456789')];
    }

    /** 
     * @test
     * @dataProvider aws_dataProvider
     */
    public function with_mock_aws_adapter(string $path, string $expectedChecksum): void
    {
        /** @var AwsS3V3Adapter&MockInterface $awsAdapter */
        $awsAdapter = Mockery::mock(AwsS3V3Adapter::class);
        $awsAdapter->shouldReceive([
            'checksum' => 'my-aws-etag',
        ]);

        $this->cacheAdapter = new CacheAdapter($awsAdapter, $this->cachePool);

        $actualResult = $this->cacheAdapter->checksum($path, new Config);

        self::assertEquals($expectedChecksum, $actualResult);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function aws_dataProvider(): iterable
    {
        yield 'checksum is cached' => ['fully-cached-file-with-checksum', 'my-cached-checksum'];
        yield 'checksum is not cached but aws ETag is' => ['partially-cached-file-with-aws-etag', 'my-cached-aws-etag'];
        yield 'file is not cached' => ['non-cached-file', 'my-aws-etag'];
    }

    /**
     * @test
     * @dataProvider errorDataProvider
     */
    public function error(string $path): void
    {
        $this->expectException(UnableToProvideChecksum::class);

        $this->cacheAdapter->checksum($path, new Config);
    }

    /**
     * 
     * @return iterable<array<mixed>>
     */
    public static function errorDataProvider(): iterable
    {
        yield 'File not found' => ['nonexistingfile'];
        yield 'Path is directory (cached)' => ['cached-directory'];
        yield 'Path is directory (non-cached)' => ['non-cached-directory'];
    }

    /** 
     * @test
     */
    public function cache_is_purged_after_unsuccessful_get(): void
    {
        $path = 'partially-cached-deleted-file';

        try {
            $this->cacheAdapter->checksum($path, new Config);
        } catch (UnableToProvideChecksum $e) {
        }

        $this->assertCachedItems([
            $path => \null,
        ]);
    }
}
