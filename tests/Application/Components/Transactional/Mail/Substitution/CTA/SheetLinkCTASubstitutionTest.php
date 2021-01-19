<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution\CTA;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA\SheetLinkCTASubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\SheetLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCompleteProfileMailView;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetLinkCTASubstitutionTest extends TestCase
{
    public function testSubstituteWithoutLink()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);

        $mail = new PrepareUserCompleteProfileMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $participant->reveal()
        );

        $sheetLinkSubstitution = $this->prophesize(SheetLinkSubstitution::class);
        $engine = $this->prophesize(TemplatingAdapterInterface::class);
        $sheetLinkSubstitution->substitute($mail)->shouldBeCalled()->willReturn('');
        $engine->render(Argument::any())->shouldNotBeCalled();

        $substitute = new SheetLinkCTASubstitution($sheetLinkSubstitution->reveal(), $engine->reveal());
        $result = $substitute->substitute($mail);

        $this->assertEquals('', $result);
    }

    public function testSubstitute()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);

        $mail = new PrepareUserCompleteProfileMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $participant->reveal()
        );

        $sheetLinkSubstitution = $this->prophesize(SheetLinkSubstitution::class);
        $engine = $this->prophesize(TemplatingAdapterInterface::class);
        $sheetLinkSubstitution->substitute($mail)->shouldBeCalled()->willReturn('https://super-event.vimeet.proximum/fr/sheet/123');
        $engine
            ->render('MailBundle:Mail:CTA/cta.html.twig', [
                'link' => 'https://super-event.vimeet.proximum/fr/sheet/123',
                'label' => 'mail.participant.profile.link',
                'locale' => 'fr',
            ])
            ->shouldBeCalled()
            ->willReturn('<a href="test">result</a>')
        ;

        $substitute = new SheetLinkCTASubstitution($sheetLinkSubstitution->reveal(), $engine->reveal());
        $result = $substitute->substitute($mail);

        $this->assertEquals('<a href="test">result</a>', $result);
    }
}
