<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Messaging\Substitutions\ParticipantTypeSubstitution;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use PHPUnit\Framework\TestCase;

class ParticipantTypeSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $locale = 'fr';
        $event  = EventFactory::createEvent();
        $type = new Type($event);
        $type->translate($locale, 'fournisseur', 'fournisseur description');
        $sheet  = SheetFactory::create($event, null, null, $type);

        $substitution = new ParticipantTypeSubstitution();
        $participantType = $substitution->getValue($sheet, $locale);

        $this->assertEquals('fournisseur', $participantType);
    }
}
