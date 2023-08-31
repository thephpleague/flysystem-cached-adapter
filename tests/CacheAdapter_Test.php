<?php

namespace tests\jgivoni\Flysystem\Cache;

use jgivoni\Flysystem\Cache\CacheAdapter;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Generator;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheAdapter_Test extends FilesystemAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $cache = new ArrayAdapter();
        $fileSystemAdapter = new InMemoryFilesystemAdapter();

        return new CacheAdapter($fileSystemAdapter, $cache);
    }

    /**
     * @test
     * @dataProvider filenameProvider2
     */
    public function writing_and_reading_files_with_special_path(string $path): void
    {
        parent::writing_and_reading_files_with_special_path($path);
    }

    /**
     * "Override" function from parent which is not static and thus incompatible with phpunit 10
     * @todo Remove when https://github.com/thephpleague/flysystem-adapter-test-utilities/commit/bf4c950b176bbefcc49c443cdab1ffb62a9fef5c is tagged (version >= 3.16?)
     * @return Generator 
     */
    public static function filenameProvider2(): Generator
    {
        yield "a path with square brackets in filename 1" => ["some/file[name].txt"];
        yield "a path with square brackets in filename 2" => ["some/file[0].txt"];
        yield "a path with square brackets in filename 3" => ["some/file[10].txt"];
        yield "a path with square brackets in dirname 1" => ["some[name]/file.txt"];
        yield "a path with square brackets in dirname 2" => ["some[0]/file.txt"];
        yield "a path with square brackets in dirname 3" => ["some[10]/file.txt"];
        yield "a path with curly brackets in filename 1" => ["some/file{name}.txt"];
        yield "a path with curly brackets in filename 2" => ["some/file{0}.txt"];
        yield "a path with curly brackets in filename 3" => ["some/file{10}.txt"];
        yield "a path with curly brackets in dirname 1" => ["some{name}/filename.txt"];
        yield "a path with curly brackets in dirname 2" => ["some{0}/filename.txt"];
        yield "a path with curly brackets in dirname 3" => ["some{10}/filename.txt"];
        yield "a path with space in dirname" => ["some dir/filename.txt"];
        yield "a path with space in filename" => ["somedir/file name.txt"];
    }
}
