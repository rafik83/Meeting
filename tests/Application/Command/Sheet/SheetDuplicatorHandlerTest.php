<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Command\Sheet\SheetDuplicator;
use Proximum\Vimeet\Application\Command\Sheet\SheetDuplicatorHandler;
use Proximum\Vimeet\Application\Components\Group\GroupDuplicator;
use Proximum\Vimeet\Application\Components\TemplateData\TemplateDataDuplicator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetsDuplicatedMail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SheetDuplicatorHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $groupDuplicator = $this->prophesize(GroupDuplicator::class);
        $templateDateDuplicator = $this->prophesize(TemplateDataDuplicator::class);
        $mailer = $this->prophesize(MailerInterface::class);
        $date = new \DateTime();

        $user = $this->prophesize(User::class);
        $admin = $this->prophesize(Admin::class);
        $admin->getEmail()
            ->shouldBeCalled()
            ->willReturn('admin@mail.fr');
        $admin->getLocale()
            ->shouldBeCalled()
            ->willReturn('fr');

        $originalEvent = $this->prophesize(Event::class);
        $event = $this->prophesize(Event::class);

        $type = $this->prophesize(Type::class);
        $type->getEvent()
            ->shouldBeCalled()
            ->willReturn($event->reveal());

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->willReturn($sheet2->reveal());
        $participant->getUser()->willReturn($user->reveal());
        $participant->getData()->willReturn([]);
        $participant->isActive()->willReturn(true);

        $sheet2->getOwner()->willReturn($user);
        $sheet2->getTitle()->willReturn('title');
        $sheet2->getRegistrationData()->willReturn([]);
        $sheet2->getData()->willReturn([]);
        $sheet2->getGroup()->willReturn(null);
        $sheet2->getParticipants()->willReturn([$participant]);

        $sheetRepository->hasSheetBeenDuplicatedByEvent($sheet1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $sheetRepository->hasSheetBeenDuplicatedByEvent($sheet2->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $expectedSheet = new Sheet(
            $event->reveal(),
            $type->reveal(),
            [],
            $user->reveal(),
            $date
        );

        $expectedParticipant = new Participant(
            $expectedSheet,
            $user->reveal(),
            [],
            true,
            $date
        );
        $expectedParticipant->setImported(true);

        $expectedSheet->setImported(true);
        $expectedSheet->setTitle('title');
        $expectedSheet->setRegistrationData([]);
        $expectedSheet->setDuplicatedFrom($sheet2->reveal());
        $expectedSheet->addParticipant($expectedParticipant);

        $templateDateDuplicator
            ->duplicateData($expectedSheet, $sheet2->reveal(), [AbstractChild::TEMPLATE_OBJECT_TYPE_MEDIA, 'product'])
            ->shouldBeCalled()
        ;

        $eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($expectedSheet))
            ->shouldBeCalled();

        $sheetRepository->add($expectedSheet)
            ->shouldBeCalled();

        $mailer->send(
            new SheetsDuplicatedMail(
                $expectedSheet->getEvent(),
                $originalEvent->reveal(),
                [$expectedSheet],
                [],
                [],
                'sender@mail.fr',
                'admin@mail.fr',
                'fr'
            )
        )->shouldBeCalled();

        $handler = new SheetDuplicatorHandler(
            $sheetRepository->reveal(),
            $eventDispatcher->reveal(),
            $groupDuplicator->reveal(),
            $templateDateDuplicator->reveal(),
            $mailer->reveal(),
            $date,
            'sender@mail.fr'
        );

        $handler->handle(
            new SheetDuplicator(
                $originalEvent->reveal(),
                [
                    $sheet1->reveal(),
                    $sheet2->reveal(),
                ],
                $admin->reveal(),
                $type->reveal()
            )
        );
    }
}
