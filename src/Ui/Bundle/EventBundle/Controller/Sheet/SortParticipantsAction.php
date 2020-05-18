<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\SortParticipants;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\SortParticipantsType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class SortParticipantsAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var EngineInterface */
    private $engine;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus,
        EngineInterface $engine,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        QueryBusInterface $queryBus,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
        $this->engine = $engine;
        $this->flashBag = $flashBag;
        $this->formFactory = $formFactory;
        $this->queryBus = $queryBus;
        $this->router = $router;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        string $key
    ): Response {
        $event = $eventDomain->getEvent();

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || $event !== $sheet->getEvent()
        ) {
            throw new AccessDeniedException();
        }

        $locale = $request->getLocale();
        $cardListView = $this->queryBus->handle(new CardListViewQuery($sheet, $userDomain->getUser(), $locale, false));

        $sortParticipants = new SortParticipants($sheet);
        $sortForm = $this->formFactory->create(
            SortParticipantsType::class,
            $sortParticipants,
            [
                'sortParticipants' => $sortParticipants,
                'submit' => true,
            ]
        );

        $sortForm->handleRequest($request);

        if ($sortForm->isSubmitted() && $sortForm->isValid()) {
            $this->commandBus->handle($sortParticipants);
            $this->flashBag->add('success', 'flash.sheet.sort_participants.success');

            return new RedirectResponse($this->router->generate('event_sheet', ['sheet' => $sheet->getId()]));
        }

        return new Response(
            $this->engine->render(
                '@Event/Sheet/sortParticipants.html.twig',
                [
                    'sheet' => $sheet,
                    'cardListView' => $cardListView,
                    'event' => $event,
                    'uid' => $key,
                    'sortForm' => $sortForm->createView(),
                ]
            )
        );
    }
}
