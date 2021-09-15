<?php

namespace Proximum\Vimeet\Application\Command\Register;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Event\PreRegisterEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantUpdatedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetTitleCheckEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Event\User\RegisteredEvent;
use Proximum\Vimeet\Application\Event\User\RegistrationStepEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ParticipantStepHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var Synchronizer
     */
    private $accountSynchronizer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param Synchronizer                   $accountSynchronizer
     * @param EventDispatcherInterface       $eventDispatcher
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     * @param UserRepositoryInterface        $userRepository
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        Synchronizer $accountSynchronizer,
        EventDispatcherInterface $eventDispatcher,
        ParticipantInfoGuesser $participantInfoGuesser,
        UserRepositoryInterface $userRepository
    ) {
        $this->sheetRepository        = $sheetRepository;
        $this->participantRepository  = $participantRepository;
        $this->accountSynchronizer    = $accountSynchronizer;
        $this->eventDispatcher        = $eventDispatcher;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->userRepository         = $userRepository;
    }

    /**
     * @param ParticipantStep $participantStep
     */
    public function handle(ParticipantStep $participantStep)
    {
        $sheetData       = $participantStep->sheet->getRegistrationData();
        $participantData = $participantStep->participant->getData();
        $templateData    = $participantStep->templateData;

        foreach ($participantStep->data as $key => $value) {
            $templateObject = $templateData
                ->getBlock(intval($participantStep->step))
                ->getObject($key);

            if ($templateObject->hasTag(Tag::PARTICIPANT_DATA)) {
                $participantData = array_merge($participantData, [$key => $value]);
            }

            if ($templateObject->hasTag(Tag::SHEET_DATA)) {
                $sheetData = array_merge($sheetData, [$key => $value]);
            }

            // set sheet title
            if ($templateObject->hasTag(Tag::SHEET_TITLE)
                && $templateObject instanceof ContentObjectInterface
            ) {
                $participantStep->sheet->setTitle($templateObject->getContentValue());
            }

            $templateData->getBlock(intval($participantStep->step))->getObject($key)->setData($value);
        }

        $participantStep->participant->setData($participantData);
        $participantStep->sheet->setRegistrationData($sheetData);

        $this->participantRepository->set($participantStep->participant);
        $this->sheetRepository->set($participantStep->sheet);

        $this->accountSynchronizer->set($templateData, $participantStep->participant->getUser());

        // send email notification when user arrive to the last step of register funnel
        $this->triggerEvent($participantStep);

        // trigger registration step process
        $this->eventDispatcher->dispatch(Events::REGISTRATION_STEP, new RegistrationStepEvent(
            $participantStep->sheet,
            $participantStep->participant,
            $participantStep->step
        ));

        // Send Sheet Update Event to recalculate completeness of the sheet
        $sheetUpdatedEvent = new SheetUpdatedEvent($participantStep->sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent);

        // Send event to check and update sheet title depends on sheet title or owner fullname settings
        $sheetTitleCheckEvent = new SheetTitleCheckEvent($participantStep->sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_TITLE_CHECK, $sheetTitleCheckEvent);

        $this->eventDispatcher->dispatch(
            Events::PARTICIPANT_UPDATED,
            new ParticipantUpdatedEvent($participantStep->participant, $templateData)
        );
    }

    /**
     * @param ParticipantStep $participantStep
     */
    private function triggerEvent(ParticipantStep $participantStep)
    {
        // check if user are in last register funnel step
        if (null === $participantStep->templateData->getNextBlockPosition($participantStep->step)) {
            $user = $participantStep->participant->getUser();

            if (!$user->isWelcomed()) {
                // trigger registered event
                $registeredEvent = new RegisteredEvent(
                    $participantStep->sheet->getEvent(),
                    $participantStep->participant->getUser(),
                    $participantStep->locale
                );

                $this->eventDispatcher->dispatch(Events::USER_REGISTERED, $registeredEvent);
                $this->userRepository->set($user->welcome());
            }

            $preRegisteredEvent = new PreRegisterEvent(
                $this->participantInfoGuesser,
                $participantStep->sheet->getEvent(),
                $user,
                $participantStep->locale,
                $participantStep->participant,
                $participantStep->sheet
            );

            $this->eventDispatcher->dispatch(Events::EVENT_PRE_REGISTERED, $preRegisteredEvent);
        }
    }
}
