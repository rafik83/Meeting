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

class DownloadEBadgeCTASubstitution
{
    /** @var UserEventTokenGenerator */
    private $userEventTokenGenerator;

    /** @var TemplatingAdapterInterface */
    private $templating;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    private const TEMPLATING_CTA_TOKEN_CONFIRMATION = 'MailBundle:Mail:CTA/downloadEBadge.html.twig';
    private const ROUTE_CTA_EBADGE_CONFIRMATION = 'event_sheet_user_badge_download';

    public function __construct(
        TemplatingAdapterInterface $templating,
        EventUrlGeneratorInterface $eventUrlGenerator,
        UserEventTokenGenerator $userEventTokenGenerator
    ) {
        $this->templating = $templating;
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->userEventTokenGenerator = $userEventTokenGenerator;
    }

    public function getCTA(MailRecipientInterface $recipient, Sheet $sheet, string $locale): string
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
            UserEventTokenType::EBADGE_CONFIRMATION
        );

        $link = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $sheet->getEvent(),
            self::ROUTE_CTA_EBADGE_CONFIRMATION,
            [
                '_locale' => $locale,
                'token' => $userEventToken->getToken(),
                'format' => 'pdf',
            ]
        );

        return $this->templating->render(self::TEMPLATING_CTA_TOKEN_CONFIRMATION, [
            'link' => $link,
            'locale' => $locale,
        ]);
    }
}
