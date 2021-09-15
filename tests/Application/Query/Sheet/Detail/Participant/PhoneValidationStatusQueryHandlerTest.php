<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet\Detail\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\PhoneValidationStatusQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\PhoneValidationStatusQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\PhoneNotValidatedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\PhoneValidatedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\PhoneValidationStatusView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class PhoneValidationStatusQueryHandlerTest extends TestCase
{
    /**
     * @dataProvider getDataSet
     *
     * @param UserEventPhone|null       $userEventPhone
     * @param PhoneValidationStatusView $expected
     */
    public function testHandle(UserEventPhone $userEventPhone = null, PhoneValidationStatusView $expected)
    {
        $participant = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $participant->getSheet()->willReturn($sheet->reveal());
        $participant->getUser()->willReturn($user->reveal());

        $checker = $this->prophesize(UserEventPhoneChecker::class);
        $checker->getValidatedUserEventPhone($user->reveal(), $event->reveal())->willReturn($userEventPhone);

        $handler = new PhoneValidationStatusQueryHandler($checker->reveal());
        $result = $handler->handle(new PhoneValidationStatusQuery($participant->reveal()));

        $this->assertEquals($result, $expected);
    }

    /**
     * @return array
     */
    public function getDataSet(): array
    {
        $userEventPhone1 = $this->prophesize(UserEventPhone::class);

        return [
            [null, new PhoneNotValidatedView()],
            [$userEventPhone1->reveal(), new PhoneValidatedView()],
            [null, new PhoneNotValidatedView()],
        ];
    }
}
