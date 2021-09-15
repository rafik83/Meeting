<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Messaging\Substitutions\CatalogOnlineDateSubstitution;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class CatalogOnlineDateSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent('Proximum');
        $event->getConfiguration()->setDates($dateTime);
        $sheet  = SheetFactory::create($event);
        $locale = 'fr';

        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        $substitution      = new CatalogOnlineDateSubstitution();
        $catalogOnlineDate = $substitution->getValue($sheet, $locale);

        $this->assertEquals($dateFormatter->format($dateTime), $catalogOnlineDate);
    }
}
