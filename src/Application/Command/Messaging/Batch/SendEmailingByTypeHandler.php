<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Application\Adapter\SendGridApiAdapterInterface;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Application\Components\Messaging\MessageFactory;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCampaignMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message as MailMessage;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;

class SendEmailingByTypeHandler
{
    /** @var BillingInfoRepositoryInterface */
    private $billingInfoRepository;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var SendGridApiAdapterInterface */
    private $mailSender;

    /** @var SubstitutionHandler */
    private $substitutionHandler;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        BillingInfoRepositoryInterface $billingInfoRepository,
        MessageFactory $messageFactory,
        MessageRepositoryInterface $messageRepository,
        SendGridApiAdapterInterface $mailSender,
        SubstitutionHandler $substitutionHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->billingInfoRepository = $billingInfoRepository;
        $this->messageFactory = $messageFactory;
        $this->messageRepository = $messageRepository;
        $this->mailSender = $mailSender;
        $this->substitutionHandler = $substitutionHandler;
        $this->dateTime = $dateTime;
    }

    public function handle(SendEmailingByType $sendEmailingByType)
    {
        $event = $sendEmailingByType->event;

        $defaultMessage = $this->messageFactory->create(
            $sendEmailingByType->event,
            $sendEmailingByType->messageId,
            $sendEmailingByType->sendEmailToTeam
        );

        $billingEmailIndexedBySheetId = [];

        if ($defaultMessage->isSendEmailToBillingInfo()) {
            $billingEmailIndexedBySheetId = $this->getBillingEmailIndexedBySheetId($sendEmailingByType->sheets);
        }

        $types = [];
        $sheetsIndexedByTypeId = [];

        foreach ($sendEmailingByType->sheets as $sheet) {
            $type = $sheet->getType();
            $types[$type->getId()] = $type;
            $sheetsIndexedByTypeId[$type->getId()][] = $sheet;
        }

        foreach ($types as $type) {
            $this->handleType(
                $defaultMessage,
                $event,
                $type,
                $sheetsIndexedByTypeId[$type->getId()],
                $billingEmailIndexedBySheetId
            );
        }
    }

    /**
     * @param Message  $defaultMessage
     * @param Event    $event
     * @param Type     $type
     * @param Sheet[]  $sheets
     * @param string[] $billingEmailIndexedBySheetId
     */
    private function handleType(
        Message $defaultMessage,
        Event $event,
        Type $type,
        array $sheets,
        array &$billingEmailIndexedBySheetId
    ): void {
        $receivers = [];
        $message = $this->getMessageByType($event, $type, $defaultMessage);

        if (null === $message) {
            return;
        }

        /** @var Sheet $sheet */
        foreach ($sheets as $sheet) {
            $receiverViews = $this->getReceiverViewsBySheet($message, $event, $sheet, $billingEmailIndexedBySheetId);

            foreach ($receiverViews as $email => $receiverView) {
                $receivers[$email] = $receiverView;
            }
        }

        $this->mailSender->send($message, $receivers);
    }

    /**
     * @param Message  $message
     * @param Event    $event
     * @param Sheet    $sheet
     * @param string[] $billingEmailIndexedBySheetId
     *
     * @return ReceiverView[] indexed by email
     */
    private function getReceiverViewsBySheet(
        Message $message,
        Event $event,
        Sheet $sheet,
        array &$billingEmailIndexedBySheetId
    ): array {
        $user = $sheet->getOwner();
        $userEmail = $user->getEmail();
        $sheetId = $sheet->getId();
        $locale = $event->getAvailableLocale($user->getLocale());

        $substitutionResult = $this->substitutionHandler->handle(
            new PrepareUserCampaignMailView(
                $event,
                $user,
                $locale,
                $sheet
            ),
            $message
        );
        $substitutions = $substitutionResult->getAllSubstitutions();

        $receivers[$userEmail] = new ReceiverView($userEmail, $substitutions, $locale);

        if ($message->isSendEmailToBillingInfo() && isset($billingEmailIndexedBySheetId[$sheetId])) {
            $billingEmail = $billingEmailIndexedBySheetId[$sheetId];
            $receivers[$billingEmail] = new ReceiverView($billingEmail, $substitutions, $locale);
        }

        return $receivers;
    }

    private function getMessageByType(Event $event, Type $type, Message $defaultMessage): ?Message
    {
        $customMessage = $this->messageRepository->getOneByEventAndTypeAndAssociatedType(
            $event,
            $defaultMessage->getName(),
            $type
        );

        if (!$customMessage instanceof MailMessage) {
            return $defaultMessage;
        }

        if (!$customMessage->isEnabled()) {
            return null;
        }

        $message = new Message(
            $event,
            $this->dateTime,
            $defaultMessage->getName(),
            $defaultMessage->isSendToEmailTeam(),
            $defaultMessage->isSendEmailToBillingInfo()
        );

        foreach ($event->getLocales() as $locale) {
            $message->translate(
                $locale,
                $customMessage->getSubject($locale),
                $customMessage->getContent($locale),
                $this->dateTime
            );
        }

        return $message;
    }

    /**
     * @param Sheet[] $sheets
     *
     * @return string[] email indexed by Sheet id
     */
    private function getBillingEmailIndexedBySheetId(array $sheets): array
    {
        $billingInfos = $this->billingInfoRepository->getBySheets($sheets);
        $billingInfosIndexedBySheetId = [];

        foreach ($billingInfos as $billingInfo) {
            $billingInfosIndexedBySheetId[$billingInfo->getSheet()->getId()] = $billingInfo->getEmail();
        }

        return $billingInfosIndexedBySheetId;
    }
}
