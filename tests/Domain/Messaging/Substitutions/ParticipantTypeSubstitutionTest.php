<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Messaging\Substitutions\ParticipantTypeSubstitution;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

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
