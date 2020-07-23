<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Notification;

use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Notification\Notification as NotificationEnum;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class NotificationManager
{
    /** @var NotificationRepositoryInterface */
    private $notificationRepository;

    public function __construct(
        NotificationRepositoryInterface $notificationRepository
    ) {
        $this->notificationRepository = $notificationRepository;
    }

    public function createTransactionPendingNotification(Sheet $sheet): Notification
    {
        return $this->create($sheet, NotificationEnum::TYPE_TRANSACTION_PENDING);
    }

    public function createSheetTranslationCompletenessNotification(Sheet $sheet): Notification
    {
        return $this->create($sheet, NotificationEnum::TYPE_SHEET_TRANSLATION_COMPLETENESS);
    }

    public function createPackageSelectedNotification(Sheet $sheet): Notification
    {
        return $this->create($sheet, NotificationEnum::TYPE_PACKAGE_SELECTED);
    }

    private function create(Sheet $sheet, string $type): Notification
    {
        $notification = new Notification($sheet, $type);
        $this->notificationRepository->add($notification);

        return $notification;
    }
}
