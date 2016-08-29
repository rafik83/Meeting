<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Payum\Paypal;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Proximum\Vimeet\Domain\Model\Payment\Notification;
use Proximum\Vimeet\Domain\Repository\Payment\NotificationRepositoryInterface;

class StoreNotificationAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * @var \DateTimeInterface
     */
    private $now;

    /**
     * @param NotificationRepositoryInterface $notificationRepository
     * @param \DateTimeInterface              $now
     */
    public function __construct(NotificationRepositoryInterface $notificationRepository, \DateTimeInterface $now)
    {
        $this->notificationRepository = $notificationRepository;
        $this->now                    = $now;
    }

    /**
     * @param mixed $request
     */
    public function execute($request)
    {
        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);

        $notification = new Notification($request->getToken()->getGatewayName(), $getHttpRequest->query, $this->now);
        $this->notificationRepository->add($notification);
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return $request instanceof Notify;
    }
}
