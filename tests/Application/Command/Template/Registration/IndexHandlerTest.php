<?php

namespace Proximum\Vimeet\Tests\Application\Command\Template\Registration;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Index;
use Proximum\Vimeet\Application\Command\Template\Registration\IndexHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Application\Command\UserEventView\Update as UpdateUserEvent;

class IndexHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $registrationTemplate = new RegistrationTemplate(
            'Registration template',
            [],
            ['fr'],
            'fr',
            new \DateTime(),
            $event
        );

        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getEvent()->shouldBeCalled()->willReturn($event);
        $participant->getUser()->shouldBeCalled()->willReturn($user->reveal());

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getParticipants()->shouldBeCalled()->willReturn([$participant->reveal()]);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetIndexerInterface = $this->prophesize(SheetIndexerInterface::class);

        $sheetRepository->getByRegistrationTemplate($registrationTemplate)->shouldBeCalled()->willReturn([$sheet->reveal()]);
        $sheetIndexerInterface->updateSheets([$sheet->reveal()])->shouldBeCalled();

        $commandBus = $this->prophesize(CommandBusInterface::class);
        $commandBus->handle(new UpdateUserEvent($user->reveal(), $event))->shouldBeCalled();

        $indexHandler = new IndexHandler($sheetRepository->reveal(), $sheetIndexerInterface->reveal(), $commandBus->reveal());
        $indexHandler->handle(new Index($registrationTemplate));
    }
}
