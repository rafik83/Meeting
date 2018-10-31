<?php

namespace Proximum\Vimeet\Domain\Messaging;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionResult;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCampaignMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class GetUsersReceiversTest extends TestCase
{
    public function test__invoke(): void
    {
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $user->getLocale()->shouldBeCalled()->willReturn('fr');
        $user->getEmail()->shouldBeCalled()->willReturn('user1@example.net');
        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');
        $message = $this->prophesize(Message::class);
        $message->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $substitutionResult = new SubstitutionResult(
            'Event %event%',
            'Bonjour %participant%',
            ['%event%' => 'event 1'],
            ['%participant%' => 'john doe']
        );

        $substitutionHandler = $this->prophesize(SubstitutionHandler::class);
        $substitutionHandler->handle(
            new PrepareUserCampaignMailView(
                $event->reveal(),
                $user->reveal(),
                'fr',
                $sheet->reveal()
            ),
            $message->reveal()
        )->shouldBeCalled()->willReturn($substitutionResult);

        $sheetGuesser = $this->prophesize(SheetGuesser::class);
        $sheetGuesser->getUserSheet($user->reveal(), $event->reveal(), 'fr')->shouldBeCalled()->willReturn($sheet->reveal());

        $getUsersReceiversTest = new GetUsersReceivers($substitutionHandler->reveal(), $sheetGuesser->reveal());
        $result = $getUsersReceiversTest([$user->reveal()], $message->reveal());
        $expectedResult = [
            'user1@example.net' =>
                new ReceiverView(
                    'user1@example.net',
                    ['%event%' => 'event 1', '%participant%' => 'john doe'],
                    'fr'
                )
        ];

        $this->assertEquals($result, $expectedResult);
    }
}
