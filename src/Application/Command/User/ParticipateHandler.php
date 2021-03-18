<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Components\TemplateData\TemplateDataDuplicator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\MustSelectPackageEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetTitleCheckEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Event\User\RegistrationEvent;
use Proximum\Vimeet\Application\Event\User\RegistrationStepEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Event\LastEventParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Domain\UserEvent\TypeResolver;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class ParticipateHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var Synchronizer */
    private $accountSynchronizer;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var TypeResolver */
    private $typeResolver;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var LastEventParticipation */
    private $lastEventParticipation;

    /** @var TemplateDataDuplicator */
    private $templateDataDuplicator;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        TypeResolver $typeResolver,
        Synchronizer $accountSynchronizer,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $dateTime,
        LastEventParticipation $lastEventParticipation,
        TemplateDataDuplicator $templateDataDuplicator
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->accountSynchronizer   = $accountSynchronizer;
        $this->eventDispatcher       = $eventDispatcher;
        $this->dateTime              = $dateTime;
        $this->typeResolver          = $typeResolver;
        $this->eventDispatcher       = $eventDispatcher;
        $this->lastEventParticipation = $lastEventParticipation;
        $this->templateDataDuplicator = $templateDataDuplicator;
    }

    /**
     * @param Participate $participate
     */
    public function handle(Participate $participate)
    {
        // Create a new sheet for this event
        $sheet = new Sheet($participate->event, $participate->type, [], $participate->user, $this->dateTime);
        $lastUserParticipation = $this
            ->lastEventParticipation
            ->getLastEventParticipation($participate->user, $participate->event)
        ;

        // Prefill sheet data from last user participation
        if (null !== $lastUserParticipation) {
            $this->getSanitizedSheetDataAndDuplicateSheetLogo($sheet, $lastUserParticipation->getSheet());
        }

        $this->typeResolver->resolve($participate->user, $participate->event, $participate->type);

        $sheetData       = [];
        $participantData = [];
        $templateData    = $participate->templateData;

        $block = $templateData->getBlock(1);

        if (null !== $block) {
            foreach ($participate->data as $key => $value) {
                $object = $block->getObject($key);

                if (null !== $object) {
                    if ($object->hasTag(Tag::PARTICIPANT_DATA)) {
                        $participantData = array_merge($participantData, [$key => $value]);
                    }

                    if ($object->hasTag(Tag::SHEET_DATA)) {
                        $sheetData = array_merge($sheetData, [$key => $value]);
                    }

                    $object->setData($value);
                }
            }
        }

        $sheet->setRegistrationData($sheetData);
        $this->sheetRepository->add($sheet);

        // Create a new participant
        $participant = new Participant(
            $sheet,
            $participate->user,
            $participantData,
            true,
            $this->dateTime
        );
        $participant->setLocale($participate->locale);
        $this->participantRepository->add($participant);

        $sheet->addParticipant($participant);

        $participate->sheet       = $sheet;
        $participate->participant = $participant;

        $this->accountSynchronizer->set($templateData, $participant->getUser());

        $this->eventDispatcher->dispatch(
            Events::REGISTRATION_STEP,
            new RegistrationStepEvent($sheet, $participant, 1)
        );

        // Send Sheet Update Event to calculate completeness of the sheet
        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent);

        $mustSelectPackageEvent = new MustSelectPackageEvent($sheet);
        $this->eventDispatcher->dispatch(Events::MUST_SELECT_PACKAGE, $mustSelectPackageEvent);

        $sheetTitleCheckEvent = new SheetTitleCheckEvent($sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_TITLE_CHECK, $sheetTitleCheckEvent);

        $this->eventDispatcher->dispatch(
            Events::USER_REGISTRATION,
            new RegistrationEvent(
                $participate->event,
                $participate->user
            )
        );
    }

    /**
     * This method filter media objects from last sheet participation and duplicate logo of previous event
     *
     * @param Sheet $sheet
     * @param Sheet $fromSheet
     */
    private function getSanitizedSheetDataAndDuplicateSheetLogo(Sheet $sheet, Sheet $fromSheet): void
    {
        $this->templateDataDuplicator->duplicateData(
            $sheet,
            $fromSheet,
            [AbstractChild::TEMPLATE_OBJECT_TYPE_MEDIA, 'product']
        );
    }
}
