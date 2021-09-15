<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CatalogAccessCheckerTest extends TestCase
{
    public function testAllowedToAccessFalseAsDateIsNull()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $catalogAccessChecker = new CatalogAccessChecker($date);
        $this->assertEquals(false, $catalogAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessFalseAsDateIsInTheFuture()
    {
        $date        = new \DateTime('2016-09-12 10:10');
        $dateCatalog = new \DateTime('2016-10-12 10:10');
        $event       = EventFactory::createEvent();
        $event->getConfiguration()->setDates($dateCatalog);

        $catalogAccessChecker = new CatalogAccessChecker($date);
        $this->assertEquals(false, $catalogAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessTrue()
    {
        $date        = new \DateTime('2016-10-14 10:10');
        $dateCatalog = new \DateTime('2016-10-12 10:10');
        $event       = EventFactory::createEvent();
        $event->getConfiguration()->setDates($dateCatalog);

        $catalogAccessChecker = new CatalogAccessChecker($date);
        $this->assertEquals(true, $catalogAccessChecker->allowedToAccess($event));
    }
}
