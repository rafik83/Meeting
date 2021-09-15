<?php

namespace Proximum\Vimeet\Domain\Package\Orders;

use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Notification\Notification as NotificationConstant;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class OrdersChecker
{
    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * OrdersChecker constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     */
    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @param Sheet $sheet
     */
    public function check(Sheet $sheet)
    {
        $this->notificationRepository->removeByType($sheet, NotificationConstant::TYPE_PACKAGE_SELECTED);
        $package = $sheet->getPackage();

        if (null !== $package
            && $package->isPassable()
            && !$sheet->hasNotCancelledOrders()
        ) {
            $this->notificationRepository->add(new Notification(
                $sheet,
                NotificationConstant::TYPE_PACKAGE_SELECTED
            ));
        }
    }
}
