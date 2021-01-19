<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution\Link;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\EventProFormaLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareOrderConfirmedMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Adapter\EventUrlGenerator;

class EventProFormaLinkSubstitutionTest extends TestCase
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
        $substitute = new EventProFormaLinkSubstitution($eventUrlGenerator->reveal());

        $result = $substitute->substitute($mail);

        $this->assertEquals('', $result);
    }

    public function testSubstitute()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $order = $this->prophesize(Order::class);
        $sheet->getId()->shouldBeCalled()->willReturn(123);
        $order->getId()->shouldBeCalled()->willReturn(321);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('en');

        $mail = new PrepareOrderConfirmedMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $order->reveal()
        );

        $eventUrlGenerator = $this->prophesize(EventUrlGenerator::class);
        $eventUrlGenerator->generateEventAbsoluteUrl(
            $event->reveal(),
            Route::ORDER_PRO_FORMA,
            [
                'sheet' => 123,
                'order' => 321,
                '_locale' => 'en',
            ])
            ->shouldBeCalled()
            ->willReturn('https://super-event.vimeet.proximum/en/sheet/123/order/321/pro-forma')
        ;
        $substitute = new EventProFormaLinkSubstitution($eventUrlGenerator->reveal());

        $result = $substitute->substitute($mail);

        $this->assertEquals('https://super-event.vimeet.proximum/en/sheet/123/order/321/pro-forma', $result);
    }
}
