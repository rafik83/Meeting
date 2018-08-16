<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Participant\ParticipantTimezone;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ParticipantTimezoneType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    public function __construct(
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(Request $request, Sheet $sheet, Participant $participant): Response
    {
        $command = new ParticipantTimezone($participant, $participant->getTimezone());
        $form = $this->formFactory->create(ParticipantTimezoneType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);

            return new RedirectResponse($this->urlGenerator->generate('event_agenda', [
                'sheet' => $sheet->getId(),
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
