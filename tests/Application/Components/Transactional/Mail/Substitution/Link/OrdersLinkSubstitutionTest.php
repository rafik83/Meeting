<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution\Link;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\OrdersLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCompleteProfileMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Adapter\EventUrlGenerator;

class OrdersLinkSubstitutionTest extends TestCase
{
    public function testSubstituteWithoutSheet()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $mail = new PrepareUserRegisteredMailView(
            $event->reveal(),
            $user->reveal(),
            'fr'
        );

        $eventUrlGenerator = $this->prophesize(EventUrlGenerator::class);
        $substitute = new OrdersLinkSubstitution($eventUrlGenerator->reveal());

        $result = $substitute->substitute($mail);

        $this->assertEquals('', $result);
    }

    public function testSubstitute()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $sheet->getId()->shouldBeCalled()->willReturn(123);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('en');

        $mail = new PrepareUserCompleteProfileMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $participant->reveal()
        );

        $eventUrlGenerator = $this->prophesize(EventUrlGenerator::class);
        $eventUrlGenerator->generateEventAbsoluteUrl(
            $event->reveal(),
            Route::ORDER_LIST,
            [
                'sheet' => 123,
                '_locale' => 'en',
            ])
            ->shouldBeCalled()
            ->willReturn('https://super-event.vimeet.proximum/en/sheet/123/orders')
        ;
        $substitute = new OrdersLinkSubstitution($eventUrlGenerator->reveal());

        $result = $substitute->substitute($mail);

        $this->assertEquals('https://super-event.vimeet.proximum/en/sheet/123/orders', $result);
    }
}
