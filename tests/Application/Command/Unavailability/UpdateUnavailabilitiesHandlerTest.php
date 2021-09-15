<?php

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Unavailability\RemoveUserUnavailabilities;
use Proximum\Vimeet\Application\Command\Unavailability\UpdateUnavailabilities;
use Proximum\Vimeet\Application\Command\Unavailability\UpdateUnavailabilitiesHandler;
use Proximum\Vimeet\Application\Command\Unavailability\Create;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Unavailability\CreateHandler;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultsView;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UpdateUnavailabilitiesHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $day1 = $this->prophesize(Event\Day::class);
        $day2 = $this->prophesize(Event\Day::class);
        $event->getDays()->willReturn([$day1, $day2]);
        $event->getFirstDay()->willReturn($day1);

        $payload = [
            [
                'day' => [
                    'start' => '1551171600',
                    'end' => '1551200400',
                ],
                'unavailabilities' => [
                    [
                        'begin' => [
                            'hour' => 10,
                            'minute' => 0,
                        ],
                        'end' => [
                            'hour' => 10,
                            'minute' => 30,
                        ],
                    ],
                    [
                        'begin' => [
                            'hour' => 11,
                            'minute' => 0,
                        ],
                        'end' => [
                            'hour' => 11,
                            'minute' => 30,
                        ],
                    ],
                ],
            ],
            [
                'day' => [
                    'start' => '1551258000',
                    'end' => '1551286800',
                ],
                'unavailabilities' => [
                    [
                        'begin' => [
                            'hour' => 10,
                            'minute' => 0,
                        ],
                        'end' => [
                            'hour' => 18,
                            'minute' => 0,
                        ],
                    ],
                ],
            ],
        ];

        $date1Start = (new \DateTime())
            ->setTimestamp('1551171600')
            ->setTimezone(new \DateTimeZone('UTC')); // 26/2/2019 à 9:00:00
        $date1End = (new \DateTime())
            ->setTimestamp('1551200400')
            ->setTimezone(new \DateTimeZone('UTC')); // 26/2/2019 à 17:00:00

        $date2Start = (new \DateTime())
            ->setTimestamp('1551258000')
            ->setTimezone(new \DateTimeZone('UTC')); //  27/2/2019 à 9:00:00
        $date2End = (new \DateTime())
            ->setTimestamp('1551286800')
            ->setTimezone(new \DateTimeZone('UTC')); // 27/2/2019 à 17:00:00

        $day1->getStartTime()->willReturn($date1Start);
        $day1->getEndTime()->willReturn($date1End);
        $day2->getStartTime()->willReturn($date2Start);
        $day2->getEndTime()->willReturn($date2End);

        $getTimezoneHelper = $this->prophesize(GetTimezoneHelper::class);
        $getTimezoneHelper->getTimezoneByEventAndParticipant($event->reveal(), $participant->reveal())
            ->shouldBeCalled()
            ->willReturn('Europe/Paris');

        $commandBus = $this->prophesize(CommandBusInterface::class);
        $createHandler = $this->prophesize(CreateHandler::class);

        $create1 = new Create(
            $event->reveal(),
            $sheet->reveal(),
            $user->reveal(),
            'fr',
            'UTC'
        );
        $create1->day = $day1->reveal();
        $create1->time = [
            'begin' => [
                'hour' => 9,
                'minute' => 0,
            ],
            'end' => [
                'hour' => 9,
                'minute' => 30,
            ],
        ];

        $create2 = new Create(
            $event->reveal(),
            $sheet->reveal(),
            $user->reveal(),
            'fr',
            'UTC'
        );
        $create2->day = $day1->reveal();
        $create2->time = [
            'begin' => [
                'hour' => 10,
                'minute' => 0,
            ],
            'end' => [
                'hour' => 10,
                'minute' => 30,
            ],
        ];

        $create3 = new Create(
            $event->reveal(),
            $sheet->reveal(),
            $user->reveal(),
            'fr',
            'UTC'
        );
        $create3->day = $day2->reveal();
        $create3->time = [
            'begin' => [
                'hour' => 9,
                'minute' => 0,
            ],
            'end' => [
                'hour' => 17,
                'minute' => 0,
            ],
        ];

        $removeUserUnavailabilitiesQuery = new RemoveUserUnavailabilities($user->reveal(), $event->reveal(), $sheet->reveal());

        $commandBus->handle($removeUserUnavailabilitiesQuery)->shouldBeCalled();
        $createHandler->handle($create1)->shouldBeCalled();
        $createHandler->handle($create2)->shouldBeCalled();
        $createHandler->handle($create3)->shouldBeCalled();

        $handler = new UpdateUnavailabilitiesHandler($getTimezoneHelper->reveal(), $commandBus->reveal(), $createHandler->reveal());
        $result = $handler->handle(new UpdateUnavailabilities(
            $event->reveal(),
            $sheet->reveal(),
            $user->reveal(),
            'fr',
            $participant->reveal(),
            $payload
        ));

        $expectedResult = new CreateUnavailabilitiesResultsView([
            new CreateUnavailabilitiesResultView($day1->reveal(), true),
            new CreateUnavailabilitiesResultView($day1->reveal(), true),
            new CreateUnavailabilitiesResultView($day2->reveal(), true),
        ]);

        $this->assertEquals($result, $expectedResult);
    }
}
