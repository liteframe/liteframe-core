<?php

use LiteFrame\Storage\Config;
use LiteFrame\Testing\TestCase;

class ConfigTest extends TestCase
{

    public function testDefault()
    {
        $value = Config::get('app.missing', 'LiteFrame');
        $this->assertEquals('LiteFrame', $value);
    }

    public function testSetAndGet()
    {
        Config::set('app.test', 'test-data');

        $value = Config::get('app.test');
        $this->assertEquals('test-data', $value);
    }

    public function testSetAndGetWithoutKey()
    {
        Config::set('sample', 'test-data');

        $value = Config::get('sample');
        $this->assertEquals('test-data', $value);
    }

}