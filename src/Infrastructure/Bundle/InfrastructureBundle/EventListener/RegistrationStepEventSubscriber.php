<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Application\Components\Registration\StepManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\RegistrationStepEvent;
use Proximum\Vimeet\Domain\Participant\ParticipantOfSheetWithPackageParticipantAndPlanningDisabled;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationStepEventSubscriber implements EventSubscriberInterface
{
    /** @var ParticipantOfSheetWithPackageParticipantAndPlanningDisabled */
    private $participantOfSheetWithPackageParticipantAndPlanningDisabled;

    /** @var StepManager */
    private $registrationStepManager;

    public function __construct(
        ParticipantOfSheetWithPackageParticipantAndPlanningDisabled $participantOfSheetWithPackageParticipantAndPlanningDisabled,
        StepManager $registrationStepManager
    ) {
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled = $participantOfSheetWithPackageParticipantAndPlanningDisabled;
        $this->registrationStepManager = $registrationStepManager;
    }

    /**
     * @param RegistrationStepEvent $event
     */
    public function onRegistrationStep(RegistrationStepEvent $event)
    {
        $participant = $event->getParticipant();
        $this->registrationStepManager->updateCurrentStep($participant, $event->getStep());
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled->handle($participant);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::REGISTRATION_STEP => 'onRegistrationStep',
        ];
    }
}
