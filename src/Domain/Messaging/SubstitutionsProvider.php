<?php

namespace Proximum\Vimeet\Domain\Messaging;

use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Query\Sheet\Planning\SheetPlanningViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Planning\SheetPlanningViewQueryHandler;
use Proximum\Vimeet\Domain\Messaging\Substitutions\AgendaConfirmationCTASubstitution;
use Proximum\Vimeet\Domain\Messaging\Substitutions\DownloadEBadgeCTASubstitution;
use Proximum\Vimeet\Domain\Messaging\Substitutions\TestVisioConfigurationCTASubstitution;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Compose;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

/**
 * Helper service that handles substitutions in messaging messages.
 *
 * Substitution is the act of replacing generic placeholders in message body with data specific to the recipient.
 *
 * Examples: full name of the recipient, proper link to activate his/her account, the name of the participation
 * sheet the participant belongs to, etc.
 *
 * @see Compose::getAllPlaceholders()
 */
class SubstitutionsProvider
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ActivateAccountTokenGenerator */
    private $activateAccountTokenGenerator;

    /** @var SheetPlanningViewQueryHandler */
    private $sheetPlanningViewQueryHandler;

    /** @var AgendaConfirmationCTASubstitution */
    private $agendaConfirmationCTASubstitution;

    /** @var DownloadEBadgeCTASubstitution */
    private $downloadEBadgeCTASubstitution;

    /** @var TestVisioConfigurationCTASubstitution */
    private $testVisioConfigurationCTASubstitution;

    /**
     * @param EventUrlGeneratorInterface        $eventUrlGenerator                 Event URL generator used to substitute
     *                                                                             event-related link placeholders
     * @param ParticipantInfoGuesser            $participantInfoGuesser            Service used to retrieve a participant
     *                                                                             complete name
     * @param ActivateAccountTokenGenerator     $activateAccountTokenGenerator     Service used to generate tokens for
     *                                                                             inactive users who must activate their
     *                                                                             account
     * @param SheetPlanningViewQueryHandler     $sheetPlanningViewQueryHandler     Service used to generate the sheet
     *                                                                             planning in html format
     * @param AgendaConfirmationCTASubstitution $agendaConfirmationCTASubstitution
     * @param DownloadEBadgeCTASubstitution     $downloadEBadgeCTASubstitution
     * @param TestVisioConfigurationCTASubstitution $testVisioConfigurationCTASubstitution
     */
    public function __construct(
        EventUrlGeneratorInterface $eventUrlGenerator,
        ParticipantInfoGuesser $participantInfoGuesser,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        SheetPlanningViewQueryHandler $sheetPlanningViewQueryHandler,
        AgendaConfirmationCTASubstitution $agendaConfirmationCTASubstitution,
        DownloadEBadgeCTASubstitution $downloadEBadgeCTASubstitution,
        TestVisioConfigurationCTASubstitution $testVisioConfigurationCTASubstitution
    ) {
        $this->eventUrlGenerator                 = $eventUrlGenerator;
        $this->participantInfoGuesser            = $participantInfoGuesser;
        $this->activateAccountTokenGenerator     = $activateAccountTokenGenerator;
        $this->sheetPlanningViewQueryHandler     = $sheetPlanningViewQueryHandler;
        $this->agendaConfirmationCTASubstitution = $agendaConfirmationCTASubstitution;
        $this->downloadEBadgeCTASubstitution = $downloadEBadgeCTASubstitution;
        $this->testVisioConfigurationCTASubstitution = $testVisioConfigurationCTASubstitution;
    }

    /**
     * Get the list of substitutions to apply for a given recipient, sheet, locale and placeholders to fill in
     *
     * @param MailRecipientInterface $recipient    Participant, sheet owner or billing contact
     * @param Sheet                  $sheet        The participation sheet the mailing message is part of
     * @param string                 $locale       Recipient's locale
     * @param string[]               $placeholders Placeholders to provide substitutions for
     *
     * @return string[] List of replaced values indexed by their placeholder
     */
    public function getSubstitutions(MailRecipientInterface $recipient, Sheet $sheet, $locale, $placeholders = [])
    {
        $substitutions = [];

        foreach ($placeholders as $placeholder) {
            $substitutions[$placeholder] = $this->getSubstitution($placeholder, $recipient, $sheet, $locale);
        }

        return $substitutions;
    }

    /**
     * Return the list of all placeholders contained in a given message body.
     *
     * @see Compose::getAllPlaceholders()
     *
     * @param string $messageBody
     *
     * @return string[]
     */
    public function findPlaceholdersInMessage($messageBody)
    {
        $foundPlaceholders = [];

        foreach (Compose::getAllPlaceholders() as $placeholder) {
            if (false !== strpos($messageBody, $placeholder)) {
                $foundPlaceholders[] = $placeholder;
            }
        }

        return $foundPlaceholders;
    }

    /**
     * Get the substitution for a given placeholder according to recipient, participation sheet & locale.
     *
     * @param string                 $placeholder
     * @param MailRecipientInterface $recipient
     * @param Sheet                  $sheet
     * @param string                 $locale
     *
     * @return string
     */
    private function getSubstitution($placeholder, MailRecipientInterface $recipient, Sheet $sheet, $locale)
    {
        if (!\in_array($placeholder, Compose::getAllPlaceholders())) {
            throw new InvalidMessagePlaceholderException($placeholder);
        }

        $event = $sheet->getEvent();

        switch ($placeholder) {
            case Compose::TAG_EVENT_NAME:
                return $event->getTitle();
            case Compose::TAG_PARTICIPATION_TYPE:
                return $sheet->getType()->getTitle($locale);
            case Compose::TAG_PARTICIPANT:
                if ($recipient instanceof Participant) {
                    return $this->participantInfoGuesser->guessParticipantCompleteName($recipient, $locale);
                }

                return $recipient->getFullname();
            case Compose::TAG_SHEET_PLANNING:
                return $this->handleSheetPlanning($sheet, $locale, $recipient);
            case Compose::TAG_SHEET_SPOT:
                return $sheet->hasSpot() ? $sheet->getSpot()->getReference() : '';
            case Compose::TAG_CTA_AGENDA_CONFIRMATION:
                return $this->agendaConfirmationCTASubstitution->getCTA($recipient, $sheet, $locale);
            case Compose::TAG_CTA_TEST_VISIO_CONFIGURATION:
                return $this->testVisioConfigurationCTASubstitution->getCTA($sheet, $locale);
            case Compose::LINK_ACTIVACTE_ACCOUNT:
                return $this->getActivateAccountUrl($recipient, $sheet, $locale);
            case Compose::LINK_AGENDA:
                return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_agenda', [
                    'sheet'   => $sheet->getId(),
                    '_locale' => $locale,
                ]);
            case Compose::LINK_CATALOG:
                return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_catalog_index', [
                    'sheet'   => $sheet->getId(),
                    '_locale' => $locale,
                ]);
            case Compose::LINK_MEETING_REQUEST:
                return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_meeting_list_request', [
                    '_locale' => $locale,
                    'sheet'   => $sheet->getId(),
                ]);
            case Compose::LINK_ORDERS:
                return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_order_list', [
                    '_locale' => $locale,
                    'sheet'   => $sheet->getId(),
                ]);
            case Compose::LINK_PACKAGE:
                return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_package_redirect_depending_on_context', [
                    'sheet'   => $sheet->getId(),
                    '_locale' => $locale,
                ]);
            case Compose::LINK_PROGRAM:
                return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'happening_program', [
                    'sheet'   => $sheet->getId(),
                    '_locale' => $locale,
                ]);
            case Compose::LINK_SHEET:
                return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_sheet_default', [
                    'sheet'   => $sheet->getId(),
                    '_locale' => $locale,
                ]);
            case Compose::LINK_EXPORT_MEETING_SHEET:
                return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_meeting_request_export_contact', [
                    'sheet'   => $sheet->getId(),
                    '_locale' => $locale,
                ]);
            case Compose::LINK_VALIDATE_MOBILE_PHONE:
                $participant = $recipient instanceof Participant ?
                    $recipient :
                    $sheet->getParticipantOwner();

                if (null === $participant) {
                    return '';
                }

                return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_user_phone_validate', [
                        'sheet'       => $sheet->getId(),
                        'participant' => $participant->getId(),
                        '_locale'     => $locale,
                    ]
                );
            case Compose::TAG_CTA_EBADGE:
                return $this->downloadEBadgeCTASubstitution->getCTA($recipient, $sheet, $locale);
        }

        throw new InvalidMessagePlaceholderException($placeholder);
    }

    /**
     * Return the URL to event login page (or activate account page when user is inactive)
     *
     * @param MailRecipientInterface $recipient
     * @param Sheet                  $sheet
     * @param string                 $locale
     *
     * @return string
     */
    private function getActivateAccountUrl(MailRecipientInterface $recipient, Sheet $sheet, $locale)
    {
        $event = $sheet->getEvent();

        // If recipient is billing contact:
        if ($recipient instanceof BillingInfo) {
            return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_login', ['_locale' => $locale]);
        }

        // Owner who does not participate:
        if ($recipient instanceof User && null === $sheet->getParticipantOwner()) {
            return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_login', ['_locale' => $locale]);
        }

        // Participant or participating owner: distinguish between inactive and active users:
        $user = $recipient instanceof Participant ? $recipient->getUser() : $recipient;

        return $user->isActive() ?
            $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_login', ['_locale' => $locale]) :
            $this->eventUrlGenerator->generateEventAbsoluteUrl(
                $event,
                'event_activate_account',
                [
                    'token'   => $this->activateAccountTokenGenerator->generate($user, $sheet)->getToken(),
                    '_locale' => $locale,
                ]
            );
    }

    /**
     * @param Sheet                  $sheet
     * @param string                 $locale
     * @param MailRecipientInterface $recipient
     *
     * @return string
     */
    private function handleSheetPlanning(Sheet $sheet, $locale, MailRecipientInterface $recipient)
    {
        $participant = null;

        if ($recipient instanceof Participant) {
            $participant = $recipient;
        }

        $sheetPlanning = $this->sheetPlanningViewQueryHandler->handle(
            new SheetPlanningViewQuery($sheet, $locale, $participant)
        );

        return $sheetPlanning->planning;
    }
}
