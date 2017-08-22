<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Messaging\Substitutions\EventSubstitution;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use PHPUnit\Framework\TestCase;

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
