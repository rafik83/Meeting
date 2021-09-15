<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
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

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    const TEMPLATING_CTA_AGENDA_CONFIRMATION = 'MailBundle:Mail:CTA/confirmAgenda.html.twig';
    const ROUTE_CTA_AGENDA_CONFIRMATION = 'event_user_event_token_confirm_agenda';

    /**
     * @param TemplatingAdapterInterface $templating
     * @param EventUrlGeneratorInterface $eventUrlGenerator
     * @param UserEventTokenGenerator    $userEventTokenGenerator
     */
    public function __construct(
        TemplatingAdapterInterface $templating,
        EventUrlGeneratorInterface $eventUrlGenerator,
        UserEventTokenGenerator $userEventTokenGenerator
    ) {
        $this->templating = $templating;
        $this->eventUrlGenerator = $eventUrlGenerator;
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

        $link = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $sheet->getEvent(),
            self::ROUTE_CTA_AGENDA_CONFIRMATION,
            [
                '_locale' => $locale,
                'token' => $userEventToken->getToken(),
            ]
        );

        return $this->templating->render(self::TEMPLATING_CTA_AGENDA_CONFIRMATION, [
            'link'   => $link,
            'locale' => $locale,
        ]);
    }
}
