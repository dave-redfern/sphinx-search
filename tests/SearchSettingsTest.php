<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Tests;

use Scorpio\SphinxSearch\ServerSettings;

/**
 * Class ServerSettingsTest
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\ServerSettingsTest
 */
class ServerSettingsTest extends BaseTest
{

    /**
     * @var ServerSettings
     */
    protected $object;

    protected function _before()
    {
        $this->object = new ServerSettings();
    }

    protected function _after()
    {
    }

    // tests

    public function testInstantiateWithoutClassWithExtensionSetsClass()
    {
        if ( !extension_loaded('sphinx') ) {
            $this->markTestSkipped('Requires Sphinx extension to be loaded to run');
        }

        $settings = new ServerSettings();

        $this->assertEquals(\SphinxClient::class, $settings->getClientClass());
    }

    public function testInstantiateWithClass()
    {
        $settings = new ServerSettings('localhost', 3232, 3000, 'MyClass');

        $this->assertEquals('MyClass', $settings->getClientClass());
    }

    public function testGetSettings()
    {
        $this->assertInternalType('array', $this->object->getSettings());
    }

    public function testSetSettings()
    {
        $this->object->setSettings([
            'host' => 'bob', 'port' => 3434,
        ]);

        $this->assertEquals('bob', $this->object->getHost());
        $this->assertEquals(3434, $this->object->getPort());
    }

    public function testSetHost()
    {
        $this->object->setHost('host');
        $this->assertEquals('host', $this->object->getHost());
    }
    
    public function testSetPort()
    {
        $this->object->setPort(9111);
        $this->assertEquals(9111, $this->object->getPort());
    }
    
    public function testSetMaxQueryTime()
    {
        $this->object->setMaxQueryTime(340);
        $this->assertEquals(340, $this->object->getMaxQueryTime());
    }

    public function testSetClientClass()
    {
        $this->object->setClientClass('bob');
        $this->assertEquals('bob', $this->object->getClientClass());
    }

    public function testConnectFailsIfNoClientClass()
    {
        $this->object->setClientClass(null);
        $this->expectException('RuntimeException');
        $this->object->connect();
    }

    public function testConnect()
    {
        $this->object->setClientClass(\SphinxClient::class);
        $conn = $this->object->connect();
        $this->assertInstanceOf(\SphinxClient::class, $conn);
    }
}




