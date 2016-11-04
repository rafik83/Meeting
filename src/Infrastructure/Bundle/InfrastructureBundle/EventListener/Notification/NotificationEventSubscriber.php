<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Notification;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Notification\SheetCompletenessEvent;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Notification\Notification as NotificationConstant;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * NotificationEventSubscriber constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     */
    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @param SheetCompletenessEvent $event
     */
    public function onSheetCompleteness(SheetCompletenessEvent $event)
    {
        $this->notificationRepository->removeByType(
            $event->getSheet(), NotificationConstant::TYPE_SHEET_TRANSLATION_COMPLETENESS
        );

        foreach($event->getNotificationCompleteness() as $completeState) {
            if ($completeState !== true) {
                $this->notificationRepository->add(new Notification(
                    $event->getSheet(),
                    NotificationConstant::TYPE_SHEET_TRANSLATION_COMPLETENESS
                ));
                break;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_COMPLETENESS => 'onSheetCompleteness',
        ];
    }
}
