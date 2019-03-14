<?php

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Unavailability\CreateUnavailabilities;
use Proximum\Vimeet\Application\Command\Unavailability\CreateUnavailabilitiesHandler;
use Proximum\Vimeet\Application\Command\Unavailability\Create;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultsView;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;

class CreateUnavailabilitiesHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $day1 = $this->prophesize(Event\Day::class);
        $day2 = $this->prophesize(Event\Day::class);

        $payload = [
            [
                'day' => [
                    'start' => '1551171600',
                    'end' => '1551200400',
                ],
                'unavailabilities' => [
                    [
                        'begin' => [
                            'hour' => 9,
                            'minute' => 0,
                        ],
                        'end' => [
                            'hour' => 9,
                            'minute' => 30,
                        ],
                    ],
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
                            'hour' => 9,
                            'minute' => 0,
                        ],
                        'end' => [
                            'hour' => 17,
                            'minute' => 0,
                        ],
                    ],
                ],
            ],
        ];

        $date1Start = (new \DateTime())
            ->setTimestamp('1551171600')
            ->setTimezone(new \DateTimeZone('UTC'));
        $date1End = (new \DateTime())
            ->setTimestamp('1551200400')
            ->setTimezone(new \DateTimeZone('UTC'));

        $date2Start = (new \DateTime())
            ->setTimestamp('1551258000')
            ->setTimezone(new \DateTimeZone('UTC'));
        $date2End = (new \DateTime())
            ->setTimestamp('1551286800')
            ->setTimezone(new \DateTimeZone('UTC'));

        $getTimezoneHelper = $this->prophesize(GetTimezoneHelper::class);
        $getTimezoneHelper->getTimezoneByEventAndParticipant($event->reveal(), $participant->reveal())
            ->shouldBeCalled()
            ->willReturn('Europe/Paris');

        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEventStartTimeAndEndTime($event->reveal(), $date1Start, $date1End)
            ->shouldBeCalled()
            ->willReturn($day1->reveal());

        $dayRepository->findByEventStartTimeAndEndTime($event->reveal(), $date2Start, $date2End)
            ->shouldBeCalled()
            ->willReturn($day2->reveal());

        $commandBus = $this->prophesize(CommandBusInterface::class);

        $create1 = new Create(
            $event->reveal(),
            $sheet->reveal(),
            $user->reveal(),
            'fr',
            'Europe/Paris'
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
            'Europe/Paris'
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
            'Europe/Paris'
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

        $commandBus->handle($create1)->shouldBeCalled();
        $commandBus->handle($create2)->shouldBeCalled();
        $commandBus->handle($create3)->shouldBeCalled();

        $handler = new CreateUnavailabilitiesHandler($getTimezoneHelper->reveal(), $dayRepository->reveal(), $commandBus->reveal());
        $result = $handler->handle(new CreateUnavailabilities(
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
