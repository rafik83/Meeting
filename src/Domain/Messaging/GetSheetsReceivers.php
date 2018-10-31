<?php

namespace Proximum\Vimeet\Domain\Messaging;

use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCampaignMailView;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class GetSheetsReceivers
{
    /** @var BillingInfoRepositoryInterface */
    private $billingInfoRepository;

    /** @var SubstitutionsProvider */
    private $substitutionsProvider;

    public function __construct(
        BillingInfoRepositoryInterface $billingInfoRepository,
        SubstitutionsProvider $substitutionsProvider
    ) {
        $this->billingInfoRepository = $billingInfoRepository;
        $this->substitutionsProvider = $substitutionsProvider;
    }

    public function __invoke(array $sheets, array $recipients, Message $message): array
    {
        $event = $message->getEvent();
        $sendToParticipants = \in_array(Campaign::RECIPIENT_PARTICIPANTS, $recipients, true);
        $sendToOwners = \in_array(Campaign::RECIPIENT_SHEET_OWNER, $recipients, true);
        $sendToBillingContacts = \in_array(Campaign::RECIPIENT_BILLING_CONTACT, $recipients, true);

        $receivers = [];
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
                $receiverView = new ReceiverView(
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
