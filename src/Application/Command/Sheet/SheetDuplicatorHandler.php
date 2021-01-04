<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Group\GroupDuplicator;
use Proximum\Vimeet\Application\Components\TemplateData\TemplateDataDuplicator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyGroupManagerOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyParticipantOrOwnerOnGroupOnSameEventException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetsDuplicatedMail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SheetDuplicatorHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var GroupDuplicator */
    private $groupDuplicator;

    /** @var MailerInterface */
    private $mailer;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var string */
    private $sender;

    /** @var TemplateDataDuplicator */
    private $templateDataDuplicator;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        EventDispatcherInterface $eventDispatcher,
        GroupDuplicator $groupDuplicator,
        TemplateDataDuplicator $templateDataDuplicator,
        MailerInterface $mailer,
        \DateTimeInterface $datetime,
        string $sender
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->groupDuplicator = $groupDuplicator;
        $this->datetime = $datetime;
        $this->mailer = $mailer;
        $this->sender = $sender;
        $this->templateDataDuplicator = $templateDataDuplicator;
    }

    public function handle(SheetDuplicator $command): void
    {
        $importedSheets = [];
        $destinationEvent = $command->type->getEvent();
        $userAlreadyGroupManagerOnSameEvent = [];
        $userAlreadyParticipantOrOwnerOnGroupOnSameEvent = [];

        foreach ($command->sheets as $sheet) {
            try {
                $duplicatedSheet = $this->duplicateSheetFrom($sheet, $destinationEvent, $command->type);

                if ($duplicatedSheet instanceof Sheet) {
                    $importedSheets[] = $duplicatedSheet;
                }
            } catch (UserAlreadyGroupManagerOnSameEventException $userAlreadyGroupManagerOnSameEventException) {
                $userAlreadyGroupManagerOnSameEvent[] = $userAlreadyGroupManagerOnSameEventException->email;
            } catch (UserAlreadyParticipantOrOwnerOnGroupOnSameEventException $userAlreadyParticipantOrOwnerOnGroupOnSameEventException) {
                $userAlreadyParticipantOrOwnerOnGroupOnSameEvent[] = $userAlreadyParticipantOrOwnerOnGroupOnSameEventException->email;
            }
        }

        $this->mailer->send(
            new SheetsDuplicatedMail(
                $destinationEvent,
                $command->originEvent,
                $importedSheets,
                $userAlreadyGroupManagerOnSameEvent,
                $userAlreadyParticipantOrOwnerOnGroupOnSameEvent,
                $this->sender,
                $command->admin->getEmail(),
                $command->admin->getLocale()
            )
        );
    }

    /**
     * @throws UserAlreadyGroupManagerOnSameEventException
     * @throws UserAlreadyParticipantOrOwnerOnGroupOnSameEventException
     */
    private function duplicateSheetFrom(Sheet $sheet, Event $destinationEvent, Type $destinationType): ?Sheet
    {
        if (true === $this->sheetRepository->hasSheetBeenDuplicatedByEvent($sheet, $destinationEvent)) {
            return null;
        }

        $group = $this->duplicateGroupFrom($sheet, $destinationEvent);
        $duplicatedSheet = Sheet::duplicateSheetFrom($sheet, $group, $destinationType, $this->datetime);

        $this->templateDataDuplicator->duplicateData(
            $duplicatedSheet,
            $sheet,
            [AbstractChild::TEMPLATE_OBJECT_TYPE_MEDIA, 'product']
        );

        $this->sheetRepository->add($duplicatedSheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($duplicatedSheet));

        return $duplicatedSheet;
    }

    /**
     * @throws UserAlreadyGroupManagerOnSameEventException
     * @throws UserAlreadyParticipantOrOwnerOnGroupOnSameEventException
     */
    private function duplicateGroupFrom(Sheet $sheet, Event $destinationEvent): ?Group
    {
        if (!$sheet->getGroup() instanceof Group) {
            return null;
        }

        return $this->groupDuplicator->duplicateToEvent($sheet->getGroup(), $destinationEvent);
    }
}
