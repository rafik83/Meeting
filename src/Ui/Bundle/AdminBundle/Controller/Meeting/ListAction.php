<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\MeetingListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Meeting\SlotChoiceType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var MeetingSlotRepositoryInterface */
    private $slotRepository;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        MeetingSlotRepositoryInterface $slotRepository,
        FormFactoryInterface $formFactory,
        \DateTimeInterface $dateTime
    ) {
        $this->queryBus = $queryBus;
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->slotRepository = $slotRepository;
        $this->formFactory = $formFactory;
        $this->dateTime = $dateTime;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $slots = $this->slotRepository->findByEvent($event);

        $locale = $event->getAvailableLocale($request->getLocale());
        $filter = [];
        $form = $this->formFactory->create(SlotChoiceType::class, $filter, [
            'slots' => $slots,
            'method' => 'GET',
            'csrf_protection' => false,
            'timeZone' => $event->getTimeZone(),
            'locale' => $locale,
        ]);

        $currentSlot = $this->getCurrentSlot($event, $slots, $form, $request);
        $meetingListView = $this->queryBus->handle(
            new MeetingListViewQuery($event, $locale, $currentSlot)
        );

        return $this->engine->renderResponse('AdminBundle:Meeting:list.html.twig', [
            'event' => $event,
            'locale' => $locale,
            'currentSlot' => $currentSlot,
            'view' => $meetingListView,
            'form' => $form->createView(),
        ]);
    }

    private function getCurrentSlot(Event $event, array $slots, FormInterface $form, Request $request): ?MeetingSlot
    {
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            return $form->getData()['slot'];
        }

        if ($event->hasDay()) {
            $days = $event->getDays();

            $slot = $this->getSlotOfTheDay($days, $slots, $form);

            if ($slot instanceof MeetingSlot) {
                return $slot;
            }
        }

        if (!empty($slots)) {
            $slot = reset($slots);
            $form->get('slot')->setData($slot);

            return $slot;
        }

        return null;
    }

    private function getSlotOfTheDay(array $days, array $slots, FormInterface $form): ?MeetingSlot
    {
        foreach ($days as $day) {
            if ($day->getBegin() <= $this->dateTime && $day->getEnd() >= $this->dateTime) {
                foreach ($slots as $slot) {
                    if ($slot->getBegin() <= $this->dateTime && $slot->getEnd() >= $this->dateTime) {
                        $form->get('slot')->setData($slot);

                        return $slot;
                    }
                }
            }
        }

        return null;
    }
}
