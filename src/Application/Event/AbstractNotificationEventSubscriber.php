<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\MessageSubjectInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractNotificationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var NotificationRepositoryInterface
     */
    protected $notificationRepository;

    /**
     * @var SheetInfoGuesser
     */
    protected $sheetInfoGuesser;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * NotificationEventListener constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     * @param SheetInfoGuesser                $sheetInfoGuesser
     * @param TranslatorInterface             $translator
     * @param RouterInterface                 $router
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorInterface $translator,
        RouterInterface $router
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->translator             = $translator;
        $this->router                 = $router;
    }

    /**
     * @param Participant             $participant
     * @param MessageSubjectInterface $messageSubject
     *
     * @return Sheet
     */
    protected function guessSheetThatParticipantHasMeetingWith(Participant $participant, MessageSubjectInterface $messageSubject)
    {
        if ($participant->getSheet() === $messageSubject->getFromSheet()) {
            return $messageSubject->getToSheet();
        } elseif ($participant->getSheet() === $messageSubject->getToSheet()) {
            return $messageSubject->getFromSheet();
        } else {
            throw new \RuntimeException('Unable to guess the sheet the participant has meeting with.');
        }
    }
}
