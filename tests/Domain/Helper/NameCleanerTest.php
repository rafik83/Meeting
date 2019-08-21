<?php

namespace Proximum\Vimeet\Tests\Domain\Helper;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Helper\NameCleaner;

class NameCleanerTest extends TestCase
{
    public function testClean()
    {
        $this->assertEquals('Jean-Paul', NameCleaner::clean('JEAN-PAUL'));
        $this->assertEquals('Jean-Paul', NameCleaner::clean('Jean-Paul'));
        $this->assertEquals('Jean-Paul', NameCleaner::clean('jean-paul'));
        $this->assertEquals('Jean Paul', NameCleaner::clean('jean paul'));
        $this->assertEquals('Jean', NameCleaner::clean('jean'));
    }
}
