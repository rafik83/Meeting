<?php

namespace Proximum\Vimeet\Tests\Domain\Helper;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Helper\NameCleaner;

class NameCleanerTest extends TestCase
{
    public function testClean()
    {
        $this->assertEquals('Jean-Paul', NameCleaner::cleanFirstName('JEAN-PAUL'));
        $this->assertEquals('Jean-Paul', NameCleaner::cleanFirstName('Jean-Paul'));
        $this->assertEquals('Jean-Paul', NameCleaner::cleanFirstName('jean-paul'));
        $this->assertEquals('Jean Paul', NameCleaner::cleanFirstName('jean paul'));
        $this->assertEquals('Jean', NameCleaner::cleanFirstName('jean'));

        $this->assertEquals('MARTIN', NameCleaner::cleanLastName('Martin'));
        $this->assertEquals('DE LA ROCHEFOUCAULT', NameCleaner::cleanLastName('de la rochefoucault'));
    }
}
