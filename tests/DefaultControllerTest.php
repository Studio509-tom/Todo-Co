<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testHomePageIsSuccessful()
    {
        $this->assertSame('test', $_ENV['APP_ENV']);
;
    }
    
}