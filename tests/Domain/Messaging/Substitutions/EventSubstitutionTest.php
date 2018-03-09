<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Messaging\Substitutions\EventSubstitution;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class EventSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $event = EventFactory::createEvent('Proximum');
        $sheet = SheetFactory::create($event);
        $locale = 'fr';

        $eventSubstitution = new EventSubstitution();
        $eventTitle = $eventSubstitution->getValue($sheet, $locale);

        $this->assertEquals('Proximum', $eventTitle);
    }
}
