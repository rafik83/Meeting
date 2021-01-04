<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Application\Adapter\EmailingSenderInterface;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Exception\Messaging\CampaignSendingFailedException;
use Proximum\Vimeet\Domain\Messaging\GetSheetsReceivers;
use Proximum\Vimeet\Domain\Messaging\GetUsersReceivers;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;

class ProcessHandler
{
    /** @var CampaignRepositoryInterface */
    private $campaignRepository;

    /** @var EmailingSenderInterface */
    private $mailer;

    /** @var GetUsersReceivers */
    private $getUsersReceivers;

    /** @var GetSheetsReceivers */
    private $getSheetsReceivers;

    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        EmailingSenderInterface $mailer,
        GetUsersReceivers $getUsersReceivers,
        GetSheetsReceivers $getSheetsReceivers,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->mailer = $mailer;
        $this->getUsersReceivers = $getUsersReceivers;
        $this->getSheetsReceivers = $getSheetsReceivers;
        $this->sheetIndexer = $sheetIndexer;
    }

    public function handle(Process $command): void
    {
        $campaign = $command->getCampaign();
        $sheets = $campaign->getSheets();
        $users = $campaign->getUsers();

        if (!$sheets && !$users) {
            throw new CampaignSendingFailedException('flash.messaging.campaign.send.failure.no_sheet');
        }

        if (!$message = $campaign->getMessage()) {
            throw new CampaignSendingFailedException('flash.messaging.campaign.send.failure.no_message');
        }

        if (!$recipients = $campaign->getRecipients()) {
            throw new CampaignSendingFailedException('flash.messaging.campaign.send.failure.no_recipient');
        }

        $getUsersReceivers = $this->getUsersReceivers;
        $getSheetsReceivers = $this->getSheetsReceivers;

        $receivers = $users ? $getUsersReceivers($users, $message) : $getSheetsReceivers($sheets, $recipients, $message);
        $this->mailer->send($message, $receivers);

        $campaign->markAsProcessed();
        $this->campaignRepository->set($campaign);

        $this->sheetIndexer->updateSheets($sheets);
    }
}
