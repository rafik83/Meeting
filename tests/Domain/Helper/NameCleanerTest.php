<?php

namespace Proximum\Vimeet\Tests\Domain\Helper;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Helper\NameCleaner;

class NameCleanerTest extends TestCase
{
    public function testClean()
    {
        $this->assertEquals('Jean-Paul Rouve', NameCleaner::clean('Jean-Paul Rouve'));
        $this->assertEquals('Jean-Paul ROUVE', NameCleaner::clean('jean-paul ROUVE'));
        $this->assertEquals('Jean Paul Rouve', NameCleaner::clean('jean paul rouve'));
        $this->assertEquals('Jean De La Reberdière', NameCleaner::clean('jean de la Reberdière'));
    }
}
