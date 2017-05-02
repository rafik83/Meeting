<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Messaging\Substitutions\ScheduleDateSubstitution;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ScheduleDateSubstitutionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubstitute()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent('Proximum');
        $event->getConfiguration()->setDates(null, null, $datetime);
        $sheet  = SheetFactory::create($event);
        $locale = 'fr';

        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        $substitution = new ScheduleDateSubstitution();
        $scheduleDate = $substitution->getValue($sheet, $locale);

        $this->assertEquals($dateFormatter->format($datetime), $scheduleDate);
    }
}
