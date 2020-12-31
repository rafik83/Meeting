<?php

namespace Proximum\Vimeet\tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\TypeTitleSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareParticipantAddedMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class TypeTitleSubstitutionTest extends TestCase
{
    public function testSubstituteWithoutSheet()
    {
        $prepareMail = $this->prophesize(PrepareUserRegisteredMailView::class);
        $prepareMail->hasSheet()->shouldBeCalled()->willReturn(false);

        $typeTitleSubstitution = new TypeTitleSubstitution();

        $expected = '';
        $result = $typeTitleSubstitution->substitute($prepareMail->reveal());

        $this->assertEquals($expected, $result);
    }

    public function testSubstituteWithSheet()
    {
        $locale = 'fr';

        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale($locale)->shouldBeCalled()->willReturn('fr');
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getTypeTitle($locale)->shouldBeCalled()->willReturn('type title');
        $guest = $this->prophesize(Participant::class);

        $prepareMail = new PrepareParticipantAddedMailView(
            $event->reveal(),
            $user->reveal(),
            $locale,
            $sheet->reveal(),
            $guest->reveal()
        );

        $typeTitleSubstitution = new TypeTitleSubstitution();

        $expected = 'type title';
        $result = $typeTitleSubstitution->substitute($prepareMail);

        $this->assertEquals($expected, $result);
    }
}
