<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch;

/**
 * Class ServerSettings
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\ServerSettings
 * @author     Dave Redfern <info@somnambulist.tech>
 */
class ServerSettings
{

    /**
     * @var array
     */
    private $settings = [];



    /**
     * Constructor.
     *
     * @param string  $host
     * @param string  $port
     * @param integer $maxQueryTime
     * @param string  $clientClass  (defaults to \SphinxClient if the extension is loaded)
     */
    public function __construct($host = 'localhost', $port = '3312', $maxQueryTime = 5000, $clientClass = null)
    {
        $this->settings = [
            'host'           => $host,
            'port'           => $port,
            'max_query_time' => $maxQueryTime,
            'client_class'   => null,
        ];

        if ( null === $clientClass && extension_loaded('sphinx') ) {
            $this->settings['client_class'] = \SphinxClient::class;
        }
        if ( null !== $clientClass ) {
            $this->settings['client_class'] = $clientClass;
        }
    }

    /**
     * Creates a new connection to Sphinx
     *
     * Returns an object with a \SphinxClient like interface
     * 
     * @return \SphinxClient
     */
    public function connect()
    {
        $class = $this->getClientClass();
        
        if ( !class_exists($class) ) {
            throw new \RuntimeException(sprintf('Sphinx client class "%s" could not be found', $class));
        }
        
        /** @var \SphinxClient $sphinx */
        $sphinx = new $class();
        $sphinx->setServer($this->getHost(), $this->getPort());
        $sphinx->setMaxQueryTime($this->getMaxQueryTime());
        
        return $sphinx;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     *
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }



    /**
     * @return string
     */
    public function getHost()
    {
        return $this->settings['host'];
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->settings['host'] = $host;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->settings['port'];
    }

    /**
     * @param string $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->settings['port'] = $port;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getMaxQueryTime()
    {
        return $this->settings['max_query_time'];
    }

    /**
     * @param integer $maxQueryTime
     *
     * @return $this
     */
    public function setMaxQueryTime($maxQueryTime)
    {
        $this->settings['max_query_time'] = intval($maxQueryTime);

        return $this;
    }

    /**
     * @return string
     */
    public function getClientClass()
    {
        return $this->settings['client_class'];
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function setClientClass($class)
    {
        $this->settings['client_class'] = $class;

        return $this;
    }
}
