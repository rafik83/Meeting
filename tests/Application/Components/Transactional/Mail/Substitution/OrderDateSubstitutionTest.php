<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\OrderDateSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareOrderConfirmedMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class OrderDateSubstitutionTest extends TestCase
{
    public function testSubstituteWithoutSheet()
    {
        $event = $this->prophesize(Event::class);
        $event->getTimeZone()->willReturn('Europe/Paris');
        $user = $this->prophesize(User::class);

        $mail = new PrepareUserRegisteredMailView(
            $event->reveal(),
            $user->reveal(),
            'fr'
        );

        $substitute = new OrderDateSubstitution();
        $result = $substitute->substitute($mail);

        $this->assertEquals('', $result);
    }

    public function testSubstituteWithOrder()
    {
        $event = $this->prophesize(Event::class);
        $event->getTimeZone()->willReturn('Europe/Paris');
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $order = $this->prophesize(Order::class);
        $date = new \DateTime('2018-10-10 10:00:00.000');
        $order->getCreatedAt()->shouldBeCalled()->willReturn($date);

        $mail = new PrepareOrderConfirmedMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $order->reveal()
        );

        $substitute = new OrderDateSubstitution();
        $result = $substitute->substitute($mail);

        $this->assertEquals('10 oct. 2018', $result);
    }
}
