<?php

namespace Proximum\Vimeet\Tests\Domain\User\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Tip\ConfirmationPhoneTipChecker;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class ValidationRequiredCheckerTest extends TestCase
{
    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user  = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $type  = $this->prophesize(Type::class);

        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getType()->willReturn($type->reveal());

        $ddayGuesser                 = $this->prophesize(DDayGuesser::class);
        $confirmationPhoneTipChecker = $this->prophesize(ConfirmationPhoneTipChecker::class);
        $userEventPhoneChecker       = $this->prophesize(UserEventPhoneChecker::class);

        $validationRequiredChecker = new ValidationRequiredChecker(
            $ddayGuesser->reveal(),
            $confirmationPhoneTipChecker->reveal(),
            $userEventPhoneChecker->reveal()
        );

        $ddayGuesser->isItDDayAndFeatureEnabled($event->reveal())->shouldBeCalled()->willReturn(true);

        $confirmationPhoneTipChecker->isEnabled(
            $event->reveal(),
            $type->reveal()
        )->shouldBeCalled()->willReturn(true);

        $userEventPhoneChecker->isValidated(
            $user->reveal(),
            $event->reveal()
        )->shouldBeCalled()->willReturn(true);

        $validationRequired = $validationRequiredChecker->handle($sheet->reveal(), $user->reveal());

        $this->assertEquals(false, $validationRequired);
    }
}
