<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\MassUnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\MassUnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class MassUnavailabilityViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $category = new Unavailability\Category($event, 'picto', 'title', 'leftColor', 'rightColor');
        $begin    = new \DateTime('2016-10-12 10:00:00');
        $end      = new \DateTime('2016-10-12 12:00:00');
        $mass     = new Unavailability\Mass($event, $category, 'name', $begin, $end, true);
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);
        $mass->createTranslation('fr', 'titre', 'description');

        $reflection = new \ReflectionClass(Unavailability\Mass::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($mass, 1);
        $property->setAccessible(false);

        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldNotBeCalled();
        $handler = new MassUnavailabilityViewQueryHandler(
            $massAssignmentRepository->reveal(),
            $meetingPublishedAccessChecker->reveal()
        );

        $result = $handler->handle(new MassUnavailabilityViewQuery(
            $mass,
            $event,
            $participant,
            'fr'
        ));

        // Expected
        $expected = new MassUnavailabilityView(
            1,
            $begin,
            $end,
            'titre',
            'description',
            'picto',
            'leftColor',
            'rightColor',
            'Europe/Paris',
            true
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithDispatchButMeetingNotPublished()
    {
        $event    = EventFactory::createEvent();
        $category = new Unavailability\Category($event, 'picto', 'title', 'leftColor', 'rightColor');
        $begin    = new \DateTime('2016-10-12 10:00:00');
        $end      = new \DateTime('2016-10-12 12:00:00');
        $mass     = new Unavailability\Mass($event, $category, 'name', $begin, $end, true, true);
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);
        $mass->createTranslation('fr', 'titre', 'description');

        $reflection = new \ReflectionClass(Unavailability\Mass::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($mass, 1);
        $property->setAccessible(false);

        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $massAssignmentRepository->find($mass, $participant)->shouldNotBeCalled();
        $meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);
        $handler = new MassUnavailabilityViewQueryHandler(
            $massAssignmentRepository->reveal(),
            $meetingPublishedAccessChecker->reveal()
        );

        $result = $handler->handle(new MassUnavailabilityViewQuery(
            $mass,
            $event,
            $participant,
            'fr'
        ));

        // Expected
        $expected = new MassUnavailabilityView(
            1,
            $begin,
            $end,
            'titre',
            'description',
            'picto',
            'leftColor',
            'rightColor',
            'Europe/Paris',
            true
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithDispatch()
    {
        $event    = EventFactory::createEvent();
        $category = new Unavailability\Category($event, 'picto', 'title', 'leftColor', 'rightColor');
        $begin    = new \DateTime('2016-10-12 10:00:00');
        $end      = new \DateTime('2016-10-12 12:00:00');
        $end2     = new \DateTime('2016-10-12 11:00:00');
        $mass     = new Unavailability\Mass($event, $category, 'name', $begin, $end, true, true);
        $timeSlot = new Unavailability\MassTimeSlot($mass, $begin, $end2);
        $mass->createTranslation('fr', 'titre', 'description');
        $sheet       = SheetFactory::create();
        $participant = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);
        $participant->getUser()->willReturn($user->reveal());
        $participant->getSheet()->willReturn($sheet);
        $massAssignment = new Unavailability\MassAssignment($mass, $user->reveal(), $begin, $end2);

        $reflection = new \ReflectionClass(Unavailability\Mass::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($mass, 1);
        $property->setAccessible(false);

        $massAssignmentRepository = $this->prophesize(MassAssignmentRepositoryInterface::class);
        $massAssignmentRepository->find($mass, $participant)->shouldBeCalled()->willReturn($massAssignment);
        $meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);
        $handler = new MassUnavailabilityViewQueryHandler(
            $massAssignmentRepository->reveal(),
            $meetingPublishedAccessChecker->reveal()
        );

        $result = $handler->handle(new MassUnavailabilityViewQuery(
            $mass,
            $event,
            $participant->reveal(),
            'fr'
        ));

        // Expected
        $expected = new MassUnavailabilityView(
            1,
            $begin,
            $end2,
            'titre',
            'description',
            'picto',
            'leftColor',
            'rightColor',
            'Europe/Paris',
            true
        );

        $this->assertEquals($expected, $result);
    }
}
