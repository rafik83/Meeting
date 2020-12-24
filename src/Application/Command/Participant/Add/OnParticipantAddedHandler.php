<?php

namespace Proximum\Vimeet\Application\Command\Participant\Add;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\UserEventView\Update;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAddParticipantEvent;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Application\Event\User\CompleteProfileEvent;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant\OnParticipantAdded as ComexposiumOnParticipantAdded;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\ParticipantOfSheetWithPackageParticipantAndPlanningDisabled;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * On Participant Added, a few event need to be dispatched
 * For example:
 *  - Email to complete profile
 *  - Email to activate Account
 *  - Email to warn the adder that the participant has been added to his/her sheet
 */
class OnParticipantAddedHandler
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var ActivateAccountTokenGenerator */
    private $activateAccountTokenGenerator;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var ParticipantOfSheetWithPackageParticipantAndPlanningDisabled */
    private $participantOfSheetWithPackageParticipantAndPlanningDisabled;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        CommandBusInterface $commandBus,
        ParticipantOfSheetWithPackageParticipantAndPlanningDisabled $participantOfSheetWithPackageParticipantAndPlanningDisabled
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->activateAccountTokenGenerator = $activateAccountTokenGenerator;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->commandBus = $commandBus;
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled = $participantOfSheetWithPackageParticipantAndPlanningDisabled;
    }

    public function handle(OnParticipantAdded $command): void
    {
        if (!$command->participant->getSheet()->isOwner($command->participant->getUser())) {
            $this->handleNotOwnerOfSheet($command);
        }

        $this->commandBus->handle(new Update($command->participant->getUser(), $command->participant->getEvent()));

        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled->handle($command->participant);
    }

    private function handleNotOwnerOfSheet(OnParticipantAdded $command): void
    {
        $sheet = $command->participant->getSheet();
        $event = $command->participant->getEvent();
        $user = $command->participant->getUser();

        // Check that an SSO is activated for the event
        $ssoEnabled = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_COMEXPOSIUM_SSO_ENABLED);

        if (null === $ssoEnabled) {
            // If sso is not enabled, we send the event to warn the user
            // send to the guest
            if ($user->isActive()) {
                $this->sendCompleteProfileEvent($event, $user, $command->participant);
            } else {
                $this->sendActivationEvent($command->adder, $sheet, $user);
            }
        } else {
            $this->commandBus->handle(new ComexposiumOnParticipantAdded($event, $command->participant));
        }

        // send to the adder
        $this->sendActivationConfirmEvent($sheet, $command->participant, $command->adder);
    }

    /**
     * Send activation email to the guest with activation link
     *
     * @param User  $adder
     * @param Sheet $sheet
     * @param User  $userAdded
     */
    private function sendActivationEvent(User $adder, Sheet $sheet, User $userAdded): void
    {
        $token = $this->activateAccountTokenGenerator->generate($userAdded, $sheet);
        $activateAccountEvent = new ActivateAccountEvent(
            $userAdded,
            $adder,
            $sheet->getEvent(),
            $token,
            $sheet
        );

        $this->eventDispatcher->dispatch(Events::USER_ACCOUNT_ACTIVATED, $activateAccountEvent);
    }

    /**
     * Send confirm invitation email send to the adder
     *
     * @param Sheet       $sheet
     * @param Participant $guest
     * @param User        $adder
     */
    private function sendActivationConfirmEvent(Sheet $sheet, Participant $guest, User $adder): void
    {
        $event = new SheetAddParticipantEvent($sheet, $guest, $adder);
        $this->eventDispatcher->dispatch(Events::SHEET_ADD_PARTICIPANT_CONFIRMATION, $event);
    }

    /**
     * @param Event       $event
     * @param User        $user
     * @param Participant $participant
     */
    private function sendCompleteProfileEvent(Event $event, User $user, Participant $participant): void
    {
        $completeProfileEvent = new CompleteProfileEvent(
            $user,
            $event,
            $participant,
            $user->getLocale()
        );
        $this->eventDispatcher->dispatch(Events::USER_PROFILE_COMPLETED, $completeProfileEvent);
    }
}
