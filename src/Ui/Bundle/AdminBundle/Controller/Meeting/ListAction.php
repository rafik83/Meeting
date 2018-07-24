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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Meeting\SlotChoiceType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
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

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /**
     * @param QueryBusInterface                    $queryBus
     * @param EngineInterface                      $engine
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param MeetingSlotRepositoryInterface       $slotRepository
     * @param MeetingRepositoryInterface           $meetingRepository
     * @param FormFactoryInterface                 $formFactory
     */
    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        MeetingSlotRepositoryInterface $slotRepository,
        MeetingRepositoryInterface $meetingRepository,
        FormFactoryInterface $formFactory
    ) {
        $this->queryBus = $queryBus;
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->slotRepository = $slotRepository;
        $this->formFactory = $formFactory;
        $this->meetingRepository = $meetingRepository;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $slots = $this->slotRepository->findByEvent($event);
        $query = [];
        $form = $this->formFactory->create(SlotChoiceType::class, $query, [
            'slots' => $slots,
            'timeZone' => $event->getTimeZone(),
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        $meetings = $this
            ->meetingRepository
            ->getByEvent(
                $event,
                $request->query->getInt('page', 1),
                20,
                $event->getAvailableLocale($request->getLocale())
            );

        return $this->engine->renderResponse('AdminBundle:Meeting:list.html.twig', [
            'event'    => $event,
            'meetings' => $meetings,
        ]);
    }
}
