<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Application\Components\Registration\StepManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\RegistrationStepEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationStepEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var StepManager
     */
    private $registrationStepManager;

    /**
     * RegistrationStepEventListener constructor.
     *
     * @param StepManager $registrationStepManager
     */
    public function __construct(StepManager $registrationStepManager)
    {
        $this->registrationStepManager = $registrationStepManager;
    }

    /**
     * @param RegistrationStepEvent $event
     */
    public function onRegistrationStep(RegistrationStepEvent $event)
    {
        $this->registrationStepManager->updateCurrentStep($event->getParticipant(), $event->getStep());
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
