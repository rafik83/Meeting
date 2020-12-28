<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\ParticipantContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantContext implements Context
{
    /** @var ParticipantContextProxyInterface */
    private $participantContextProxy;

    /**
     * @param ParticipantContextProxyInterface $participantContextProxy
     */
    public function __construct(ParticipantContextProxyInterface $participantContextProxy)
    {
        $this->participantContextProxy = $participantContextProxy;
    }

    /**
     * @Given /^there is a participant for this sheet$/
     */
    public function thereIsAParticipantForThisSheet()
    {
        $sheet = $this->participantContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $participant = $this->participantContextProxy->getParticipantManager()->create($sheet->getEvent(), $sheet);
        $this->participantContextProxy->getStorage()->set('participant', $participant);
    }

    /**
     * @Given /^there is a participant for this sheet and this user$/
     */
    public function thereIsAParticipantForThisSheetAndThisUser()
    {
        $sheet = $this->participantContextProxy->getStorage()->get('sheet');
        $user = $this->participantContextProxy->getStorage()->get('user');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $participant = $this->participantContextProxy
            ->getParticipantManager()
            ->create($sheet->getEvent(), $sheet, $user)
        ;

        $this->participantContextProxy->getStorage()->set('participant', $participant);
    }

    /**
     * @Given /^there is an imported participant for this sheet and this user$/
     */
    public function thereIsAnImportedParticipantForThisSheetAndThisUser(): void
    {
        $sheet = $this->participantContextProxy->getStorage()->get('sheet');
        $user = $this->participantContextProxy->getStorage()->get('user');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $participant = $this->participantContextProxy
            ->getParticipantManager()
            ->create($sheet->getEvent(), $sheet, $user, true)
        ;

        $this->participantContextProxy->getStorage()->set('participant', $participant);
    }

    /**
     * @Given this participant is registered
     */
    public function thisParticipantIsRegistered()
    {
        $participant = $this->participantContextProxy->getStorage()->get('participant');
        $user = $this->participantContextProxy->getStorage()->get('user');

        if (null === $participant) {
            throw new \InvalidArgumentException('Missing Participant');
        }
        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $this->participantContextProxy->getParticipantManager()->setBasicRegistrationData($participant, $user->getFirstName(), $user->getLastName());
    }

    /**
     * @Given this participant has visio option activated
     */
    public function thisSheetHasVisioOptionActivated()
    {
        $participant = $this->participantContextProxy->getStorage()->get('participant');

        if (null === $participant) {
            throw new \InvalidArgumentException('Missing Participant');
        }
        $this->participantContextProxy->getParticipantManager()->setVisioEnabled($participant);
    }

    /**
     * @Given this participant has :position position
     */
    public function thisParticipantHasPosition(string $position)
    {
        /** @var Participant */
        $participant = $this->participantContextProxy->getStorage()->get('participant');
        $data = $participant->getData();
        $data['6c4a3a4f']['items'][] = $position;
        $participant->setData($data);
    }
}
