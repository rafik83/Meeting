<?php

namespace Proximum\Vimeet\Tests\Application\Query\Mail;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantMailViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy|ParticipantRepositoryInterface */
    private $participantRepository;
    /** @var ObjectProphecy|ParticipantInfoGuesser */
    private $participantInfoGuesser;
    /** @var ObjectProphecy|User */
    private $user;
    /** @var ObjectProphecy|Sheet */
    private $sheet;
    /** @var ObjectProphecy|Participant */
    private $participant;

    protected function setUp()
    {
        // data input

        $account = $this->prophesize(User\Account::class);
        $account->getFirstName()->willReturn('Kaori');
        $account->getLastName()->willReturn('Makimura');
        $this->user = $this->prophesize(User::class);
        $this->user->getAccount()->willReturn($account->reveal());
        $type = $this->prophesize(Type::class);
        $type->getTitle('fr')->willReturn('Enquêteur·trice');
        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('jp')->willReturn('fr');
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($event->reveal());
        $this->sheet->getType()->willReturn($type->reveal());
        $this->participant = $this->prophesize(Participant::class);
        $this->participant->getLocale()->willReturn('jp');
        $this->participant->getSheet()->willReturn($this->sheet->reveal());

        // dependencies

        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
    }

    public function testHandleNoSheet()
    {
        // run test

        $query = new ParticipantMailViewQuery(null, $this->user->reveal());

        $handler = new ParticipantMailViewQueryHandler(
            $this->participantRepository->reveal(),
            $this->participantInfoGuesser->reveal()
        );
        $result = $handler->handle($query);

        self::assertEquals(new ParticipantInfoView('Kaori', 'Makimura'), $result);
    }

    public function testHandleNotParticipant()
    {
        // dependencies

        $this->participantRepository->getParticipantForUserAndSheet($this->user->reveal(), $this->sheet->reveal())
            ->willReturn(null)
        ;

        // run test

        $query = new ParticipantMailViewQuery($this->sheet->reveal(), $this->user->reveal());

        $handler = new ParticipantMailViewQueryHandler(
            $this->participantRepository->reveal(),
            $this->participantInfoGuesser->reveal()
        );
        $result = $handler->handle($query);

        self::assertEquals(new ParticipantInfoView('Kaori', 'Makimura'), $result);
    }

    public function testHandleIsParticipant()
    {
        // dependencies

        $this->participantRepository->getParticipantForUserAndSheet($this->user->reveal(), $this->sheet->reveal())
            ->willReturn($this->participant->reveal())
        ;

        $this->participantInfoGuesser->guessParticipantFirstName($this->participant->reveal(), 'jp')->willReturn(
            'Kaori'
        )
        ;
        $this->participantInfoGuesser->guessParticipantLastName($this->participant->reveal(), 'jp')->willReturn(
            'Makimura'
        )
        ;

        // run test

        $query = new ParticipantMailViewQuery($this->sheet->reveal(), $this->user->reveal());

        $handler = new ParticipantMailViewQueryHandler(
            $this->participantRepository->reveal(),
            $this->participantInfoGuesser->reveal()
        );
        $result = $handler->handle($query);

        self::assertEquals(new ParticipantInfoView('Kaori', 'Makimura', 'Enquêteur·trice'), $result);
    }
}
