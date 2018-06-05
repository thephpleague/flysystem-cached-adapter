<?php

use League\Flysystem\Cached\Storage\Memcached;

class MemcachedTests extends PHPUnit_Framework_TestCase
{
    public function testLoadFail()
    {
        $client = Mockery::mock('Memcached');
        $client->shouldReceive('get')->once()->andReturn(false);
        $cache = new Memcached($client);
        $cache->load();
        $this->assertFalse($cache->isComplete('', false));
    }

    public function testLoadSuccess()
    {
        $response = json_encode([[], ['' => true]]);
        $client = Mockery::mock('Memcached');
        $client->shouldReceive('get')->once()->andReturn($response);
        $cache = new Memcached($client);
        $cache->load();
        $this->assertTrue($cache->isComplete('', false));
    }

    public function testSave()
    {
        $response = json_encode([[], []]);
        $client = Mockery::mock('Memcached');
        $client->shouldReceive('set')->once()->andReturn($response);
        $cache = new Memcached($client);
        $cache->save();
    }
}
