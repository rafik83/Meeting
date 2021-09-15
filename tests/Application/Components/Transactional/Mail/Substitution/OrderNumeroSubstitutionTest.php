<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\OrderNumeroSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareOrderConfirmedMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class OrderNumeroSubstitutionTest extends TestCase
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

        $substitute = new OrderNumeroSubstitution();
        $result = $substitute->substitute($mail);

        $this->assertEquals('', $result);
    }

    public function testSubstituteWithOrder()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $order = $this->prophesize(Order::class);
        $order->getNumero()->shouldBeCalled()->willReturn('123456789');

        $mail = new PrepareOrderConfirmedMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $order->reveal()
        );

        $substitute = new OrderNumeroSubstitution();
        $result = $substitute->substitute($mail);

        $this->assertEquals('123456789', $result);
    }
}
