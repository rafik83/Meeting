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
        $receivers  = [];
        $campaign   = $command->getCampaign();
        $sheets     = $campaign->getSheets();
        $recipients = $campaign->getRecipients();
        $message    = $campaign->getMessage();

        if (!$sheets) {
            throw new CampaignSendingFailedException('flash.messaging.campaign.send.failure.no_sheet');
        }

        if (!$message) {
            throw new CampaignSendingFailedException('flash.messaging.campaign.send.failure.no_message');
        }

        if (!$recipients) {
            throw new CampaignSendingFailedException('flash.messaging.campaign.send.failure.no_recipient');
        }

        $sendToParticipants    = in_array(Campaign::RECIPIENT_PARTICIPANTS, $recipients, true);
        $sendToOwners          = in_array(Campaign::RECIPIENT_SHEET_OWNER, $recipients, true);
        $sendToBillingContacts = in_array(Campaign::RECIPIENT_BILLING_CONTACT, $recipients, true);

        foreach ($sheets as $sheet) {
            if (true === $sendToOwners) {
                $receivers[] = $sheet->getOwner();
            }

            if (true === $sendToParticipants) {
                foreach ($sheet->getParticipants() as $participant) {
                    if (!in_array($participant->getUser(), $receivers)) {
                        $receivers[] = $participant;
                    }
                }
            }
        }

        if (true === $sendToBillingContacts) {
            $receivers = array_merge($receivers, $this->billingInfoRepository->getBySheets($sheets));
        }

        $this->mailer->send($message, $this->senderProvider->generate($campaign->getEvent()) , $receivers);

        $campaign->markAsSent();
        $this->campaignRepository->set($campaign);
    }
}
