<?php

namespace Proximum\Vimeet\Tests\Application\Query\Group\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Group\UserAvailabilitiesBuilderCache;
use Proximum\Vimeet\Application\Query\Group\Participant\AgendaDayViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\AgendaDayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Group\Participant\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\AgendaDayView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class ParticipantViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $user        = UserFactory::create();
        $sheet       = SheetFactory::create($event, $user);
        $participant = ParticipantFactory::create($sheet, $user);
        $day         = new Day($event, new \DateTime(), new \DateTime());
        $locale      = 'fr';

        $participantInfoGuesser         = $this->prophesize(ParticipantInfoGuesser::class);
        $agendaDayViewQueryHandler      = $this->prophesize(AgendaDayViewQueryHandler::class);
        $userAvailabilitiesBuilderCache = $this->prophesize(UserAvailabilitiesBuilderCache::class);

        $participantInfoGuesser
            ->guessParticipantFirstName($participant, $locale)
            ->shouldBeCalled()
            ->willReturn('Martin');

        $participantInfoGuesser
            ->guessParticipantLastName($participant, $locale)
            ->shouldBeCalled()
            ->willReturn('Durand');

        $agendaDayView      = new AgendaDayView([]);
        $agendaDayViewQuery = new AgendaDayViewQuery($day);
        $agendaDayViewQueryHandler->handle($agendaDayViewQuery)->shouldBeCalled()->willReturn($agendaDayView);

        $userAvailabilitiesBuilderCache
            ->buildAvailabilitiesByUserAndEventFromSkeleton($user, $event, [$agendaDayView])
            ->shouldBeCalled()
            ->willReturn([$agendaDayView]);

        $handler = new ParticipantViewQueryHandler(
            $participantInfoGuesser->reveal(),
            $agendaDayViewQueryHandler->reveal(),
            $userAvailabilitiesBuilderCache->reveal()
        );

        $handler->handle(new ParticipantViewQuery($participant, $event, [$day]));
    }
}
