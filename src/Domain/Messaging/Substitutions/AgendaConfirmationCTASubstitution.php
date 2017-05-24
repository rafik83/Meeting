<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Token\UserEventTokenGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class AgendaConfirmationCTASubstitution
{
    /** @var UserEventTokenGenerator */
    private $userEventTokenGenerator;

    /** @var TemplatingAdapterInterface */
    private $templating;

    const TEMPLATING_CTA_AGENDA_CONFIRMATION = 'MailBundle:Mail:CTA/confirmAgenda.html.twig';

    /**
     * @param TemplatingAdapterInterface $templating
     * @param UserEventTokenGenerator    $userEventTokenGenerator
     */
    public function __construct(
        TemplatingAdapterInterface $templating,
        UserEventTokenGenerator $userEventTokenGenerator
    ) {
        $this->templating = $templating;
        $this->userEventTokenGenerator = $userEventTokenGenerator;
    }

    /**
     * @param MailRecipientInterface $recipient
     * @param Sheet                  $sheet
     * @param string                 $locale
     *
     * @return string
     */
    public function getCTA(MailRecipientInterface $recipient, Sheet $sheet, $locale)
    {
        // If recipient is billing contact, there is no CTA
        if ($recipient instanceof BillingInfo) {
            return '';
        }

        // Owner who does not participate, there is no CTA
        if ($recipient instanceof User && null === $sheet->getParticipantOwner()) {
            return '';
        }

        $user = $recipient instanceof Participant ? $recipient->getUser() : $recipient;

        $userEventToken = $this->userEventTokenGenerator->getUserEventTokenForConfirmAgenda(
            $sheet->getEvent(),
            $user,
            UserEventTokenType::AGENDA_CONFIRMATION
        );

        return $this->templating->render(
            AgendaConfirmationCTASubstitution::TEMPLATING_CTA_AGENDA_CONFIRMATION, [
                'eventId' => $sheet->getEvent()->getId(),
                'locale' => $locale,
                'token' => $userEventToken->getToken()
            ]
        );
    }
}
