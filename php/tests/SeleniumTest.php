<?php
/*use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;*/

class phproTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        /*$host = 'http://localhost:4444/wd/hub';
        $driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
        $driver->get('http://www.phpro.org/');
        $title = 'PHP Tutorials Examples phPro - Tutorials Articles Examples Development';
        $this->assertContains($title, $this->webDriver->getTitle());
        ;*/
        /*$this->setHost('localhost');
        $this->setPort(4444);
        $this->setBrowser('firefox');
        $this->setBrowserUrl('http://www.phpro.org/');*/
    }

    /*
    *
    * Tests the title of PHPRO.ORG is correct
    *
    */
    public function testTitle()
    {

        /*$driver->getTitle()
        $this->url('http://www.phpro.org/');
        $this->assertEquals( $title, $this->title());*/
    }
}
