<?php

namespace tests\jgivoni\Flysystem\Cache;

use League\Flysystem\FileAttributes;
use League\Flysystem\Visibility;

class ListContents_Test extends CacheTestCase
{
    /** 
     * @test
     */
    public function content_list_is_cached(): void
    {
        [...$this->cacheAdapter->listContents('', \true)];

        $this->assertCachedItems([
            'fully-cached-file' => new FileAttributes('fully-cached-file', 10, Visibility::PUBLIC, \strtotime('2023-01-01 12:00:00'), 'text/plain'),
            'partially-cached-file' => new FileAttributes('partially-cached-file', 10, Visibility::PUBLIC, \strtotime('2023-01-01 12:00:00'), 'text/plain'),
            'overwritten-file' => new FileAttributes('overwritten-file', 10, Visibility::PUBLIC, \strtotime('2023-01-01 12:00:00'), 'text/plain'),
            'non-cached-file' => new FileAttributes('non-cached-file', 10, Visibility::PUBLIC, \strtotime('2023-01-01 12:00:00'), 'text/plain'),
            'cached-directory/file' => new FileAttributes('cached-directory/file', 10, Visibility::PUBLIC, \strtotime('2023-01-01 12:00:00'), 'text/plain'),
            'non-cached-directory/file' => new FileAttributes('non-cached-directory/file', 10, Visibility::PUBLIC, \strtotime('2023-01-01 12:00:00'), 'text/plain'),
            'deleted-cached-file' => new FileAttributes('deleted-cached-file', 10, Visibility::PUBLIC),
        ]);
    }
}
