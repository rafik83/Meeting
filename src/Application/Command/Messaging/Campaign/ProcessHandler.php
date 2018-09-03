<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Application\Exception\Messaging\CampaignSendingFailedException;
use Proximum\Vimeet\Domain\Messaging\SubstitutionsProvider;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\SendGridApiAdapter;

class ProcessHandler
{
    /**
     * @var BillingInfoRepositoryInterface
     */
    private $billingInfoRepository;

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var SendGridApiAdapter
     */
    private $mailer;

    /**
     * @var SubstitutionsProvider
     */
    private $substitutionsProvider;

    /**
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     * @param CampaignRepositoryInterface    $campaignRepository
     * @param SendGridApiAdapter             $mailer
     * @param SubstitutionsProvider          $substitutionsProvider
     */
    public function __construct(
        BillingInfoRepositoryInterface $billingInfoRepository,
        CampaignRepositoryInterface $campaignRepository,
        SendGridApiAdapter $mailer,
        SubstitutionsProvider $substitutionsProvider
    ) {
        $this->billingInfoRepository = $billingInfoRepository;
        $this->campaignRepository    = $campaignRepository;
        $this->mailer                = $mailer;
        $this->substitutionsProvider = $substitutionsProvider;
    }

    /**
     * @param Process $command
     */
    public function handle(Process $command)
    {
        $campaign = $command->getCampaign();

        if (!$sheets = $campaign->getSheets()) {
            throw new CampaignSendingFailedException('flash.messaging.campaign.send.failure.no_sheet');
        }

        if (!$message = $campaign->getMessage()) {
            throw new CampaignSendingFailedException('flash.messaging.campaign.send.failure.no_message');
        }

        if (!$recipients = $campaign->getRecipients()) {
            throw new CampaignSendingFailedException('flash.messaging.campaign.send.failure.no_recipient');
        }

        $this->mailer->send($message, $this->getReceivers($sheets, $recipients, $campaign->getMessage()));

        $campaign->markAsProcessed();
        $this->campaignRepository->set($campaign);
    }

    /**
     * Gets the receivers for the current messaging campaign.
     *
     * @param Sheet[]  $sheets     The Campaign sheets
     * @param string[] $recipients The Campaign recipients
     * @param Message  $message    The message to send (required to retrieve locale and also the placeholders it contains)
     *
     * @return array An array where keys are receivers email addresses
     *               and values {@link MailRecipientInterface} instances
     */
    private function getReceivers(array $sheets, array $recipients, Message $message)
    {
        $event                 = $message->getEvent();
        $sendToParticipants    = \in_array(Campaign::RECIPIENT_PARTICIPANTS, $recipients, true);
        $sendToOwners          = \in_array(Campaign::RECIPIENT_SHEET_OWNER, $recipients, true);
        $sendToBillingContacts = \in_array(Campaign::RECIPIENT_BILLING_CONTACT, $recipients, true);

        $receivers    = [];
        $addReceivers = function (Sheet $sheet, $newReceivers) use (&$receivers, $message, $event) {
            if (!\is_array($newReceivers) && !$newReceivers instanceof \Traversable) {
                return;
            }

            $placeHolders = [];

            foreach ($event->getLocales() as $locale) {
                $placeHolders[$locale] = $this
                    ->substitutionsProvider
                    ->findPlaceholdersInMessage($message->getContent($locale));
            }

            /** @var MailRecipientInterface $receiver */
            foreach ($newReceivers as $receiver) {
                if (isset($receivers[$receiver->getEmail()])) {
                    continue;
                }

                $receiverLocale = $event->getAvailableLocale($receiver->getLocale());
                $receiverView   = new ReceiverView(
                    $receiver->getEmail(),
                    $this->substitutionsProvider->getSubstitutions(
                        $receiver,
                        $sheet,
                        $receiverLocale,
                        isset($placeHolders[$receiverLocale]) ? $placeHolders[$receiverLocale] : []
                    ),
                    $receiverLocale
                );

                $receivers[$receiver->getEmail()] = $receiverView;
            }
        };

        foreach ($sheets as $sheet) {
            if (true === $sendToParticipants) {
                $addReceivers($sheet, $sheet->getParticipants());
            }

            if (true === $sendToOwners) {
                $addReceivers($sheet, [$sheet->getOwner()]);
            }
        }

        if (true === $sendToBillingContacts) {
            /* @var BillingInfo */
            foreach ($this->billingInfoRepository->getBySheets($sheets) as $billingInfo) {
                $addReceivers($billingInfo->getSheet(), [$billingInfo]);
            }
        }

        return $receivers;
    }
}
