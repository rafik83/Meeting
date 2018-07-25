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

    /**
     * @param QueryBusInterface                    $queryBus
     * @param EngineInterface                      $engine
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param MeetingSlotRepositoryInterface       $slotRepository
     * @param FormFactoryInterface                 $formFactory
     */
    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        MeetingSlotRepositoryInterface $slotRepository,
        FormFactoryInterface $formFactory
    ) {
        $this->queryBus = $queryBus;
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->slotRepository = $slotRepository;
        $this->formFactory = $formFactory;
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

        $currentSlot = $this->getCurrentSlot($slots, $form, $request);
        $meetingListView = $this->queryBus->handle(
            new MeetingListViewQuery($event, $locale, $currentSlot))
        ;

        return $this->engine->renderResponse('AdminBundle:Meeting:list.html.twig', [
            'event' => $event,
            'locale' => $locale,
            'currentSlot' => $currentSlot,
            'view' => $meetingListView,
            'form' => $form->createView(),
        ]);
    }

    private function getCurrentSlot(array $slots, FormInterface $form, Request $request): ?MeetingSlot
    {
        $currentSlot = null;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            return $form->getData()['slot'];
        }

        if (!empty($slots)) {
            return reset($slots);
        }

        return null;
    }
}
