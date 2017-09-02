<?php

namespace Scorpio\SphinxSearch\Tests;

use Helper\Unit;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseTest
 *
 * @package    Scorpio\SphinxSearch\Tests
 * @subpackage Scorpio\SphinxSearch\Tests\BaseTest
 */
abstract class BaseTest extends TestCase
{

    /**
     * @var Unit
     */
    protected $I;

    protected function setUp()
    {
        parent::setUp();

        $this->I = new Unit();

        $this->_before();
    }

    protected function tearDown()
    {
        $this->_after();

        parent::tearDown();
    }

    protected function _before()
    {

    }

    protected function _after()
    {

    }
}
