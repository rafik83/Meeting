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
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\SendGridApiAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;

class SendHandler
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
     * @var EventSender
     */
    private $senderProvider;

    public function __construct(
        BillingInfoRepositoryInterface $billingInfoRepository,
        CampaignRepositoryInterface $campaignRepository,
        SendGridApiAdapter $mailer,
        EventSender $senderProvider
    ) {
        $this->billingInfoRepository = $billingInfoRepository;
        $this->campaignRepository    = $campaignRepository;
        $this->mailer                = $mailer;
        $this->senderProvider        = $senderProvider;
    }

    /**
     * @param Send $command
     */
    public function handle(Send $command)
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

        $this->mailer->send($message, $this->senderProvider->generate($campaign->getEvent()), $this->getReceivers($sheets, $recipients));

        $campaign->markAsSent();
        $this->campaignRepository->set($campaign);
    }

    /**
     * Gets the receivers for the current messaging campaign.
     *
     * @param Sheet[]  $sheets     The Campaign sheets
     * @param string[] $recipients The Campaign recipients
     *
     * @return array $receivers An array where keys are receivers email addresses
     *                          and values {@link MailRecipientInterface} instances
     */
    private function getReceivers(array $sheets, array $recipients)
    {
        $sendToParticipants    = in_array(Campaign::RECIPIENT_PARTICIPANTS, $recipients, true);
        $sendToOwners          = in_array(Campaign::RECIPIENT_SHEET_OWNER, $recipients, true);
        $sendToBillingContacts = in_array(Campaign::RECIPIENT_BILLING_CONTACT, $recipients, true);

        $addReceivers = function ($newReceivers) use (&$receivers) {
            /* @var MailRecipientInterface */
            foreach ($newReceivers as $receiver) {
                $emailAddress = $receiver->getEmail();

                if (isset($receivers[$emailAddress])) {
                    continue;
                }

                $receivers[$receiver->getEmail()] = $receiver;
            }
        };

        foreach ($sheets as $sheet) {
            if (true === $sendToParticipants) {
                $addReceivers($sheet->getParticipants());
            }

            if (true === $sendToOwners) {
                $addReceivers([$sheet->getOwner()]);
            }
        }

        if (true === $sendToBillingContacts) {
            $addReceivers($this->billingInfoRepository->getBySheets($sheets));
        }

        return $receivers;
    }
}
