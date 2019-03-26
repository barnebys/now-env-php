<?php

namespace Barnebys\Tests;

use Barnebys\Analytics\Impression;
use Barnebys\Analytics\UrlBuilder;

class EnvTest extends TestCase
{
    public function testSecret()
    {
        $this->assertEquals("keep-it-secret", getenv('SECRET'));
    }

    public function testRequired()
    {
        $this->assertEquals("required-value", getenv('REQUIRED_KEY'));
    }


}