<?php

use League\Flysystem\Cached\Storage\Memcached;

class MemcachedTests extends PHPUnit_Framework_TestCase
{
    public function testLoadFail()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM has a bug breaking mockery');
        }

        $client = Mockery::mock('Memcached');
        $client->shouldReceive('get')->once()->andReturn(false);
        $cache = new Memcached($client);
        $cache->load();
        $this->assertFalse($cache->isComplete('', false));
    }

    /**
     * Test that data can be loaded and retrieved from the cache.
     */
    public function testLoadSuccess()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM has a bug breaking mockery');
        }

        $cachedData = [
            'test' => [
                'dirname' => '',
                'basename' => 'test',
                'filename' => 'test',
                'path' => 'test',
                'type' => 'dir',
            ],
        ];

        $response = json_encode([$cachedData, []]);
        $client = Mockery::mock('Memcached');
        $client->shouldReceive('get')->once()->andReturn($response);
        $cache = new Memcached($client);
        $cache->load();
        $this->assertEquals($cachedData['test'], $cache->getMetadata('test'));
    }

    public function testSave()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM has a bug breaking mockery');
        }

        $response = json_encode([[], []]);
        $client = Mockery::mock('Memcached');
        $client->shouldReceive('set')->once()->andReturn($response);
        $cache = new Memcached($client);
        $cache->save();
    }

    /**
     * Test that the memcache driver never ensures complete data.
     */
    public function testNeverComplete() {
        $client = Mockery::mock('Memcached');
        $cache = new Memcached($client);
        $cache->setComplete('test', true);
        $this->assertFalse($cache->isComplete('test', true));
    }
}
