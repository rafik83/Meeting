<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Participant\ParticipantTimezone;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ParticipantTimezoneType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ParticipantTimezoneAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    public function __construct(
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        UrlGeneratorInterface $urlGenerator,
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        GetTimezoneHelper $getTimezoneHelper
    ) {
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->urlGenerator = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
        $this->getTimezoneHelper = $getTimezoneHelper;
    }

    public function __invoke(Request $request, Sheet $sheet, Participant $participant): Response
    {
        if (!$sheet->hasParticipant($participant)
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedException();
        }

        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndParticipant($sheet->getEvent(), $participant);
        $command = new ParticipantTimezone($participant, $timezone);
        $form = $this->formFactory->create(ParticipantTimezoneType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);

            return new RedirectResponse($this->urlGenerator->generate('event_agenda_participant', [
                'sheet' => $sheet->getId(),
                'participant' => $participant->getId(),
            ]));
        }

        return new Response(
            $this->engine->render('@Event/Agenda/timezone.html.twig', [
                'event' => $sheet->getEvent(),
                'sheet' => $sheet,
                'form' => $form->createView(),
            ])
        );
    }
}
