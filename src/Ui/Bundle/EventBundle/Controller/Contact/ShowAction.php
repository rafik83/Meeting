<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Contact;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Contact\EditEvaluation;
use Proximum\Vimeet\Application\Query\Contact\GetContactViewQuery;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\User\Sheet\HasAccessToSheet;
use Proximum\Vimeet\Infrastructure\Adapter\CommandBus;
use Proximum\Vimeet\Infrastructure\Adapter\RouterAdapter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Contact\EvaluationType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ShowAction
{
    public const MODE_QUERY_KEY = 'mode';
    public const MODE_EDIT_EVALUATION = 'evaluation';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    /** @var HasAccessToSheet */
    private $hasAccessToSheet;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBus */
    private $commandBus;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ContactRepositoryInterface */
    private $contactRepository;

    /** @var RouterAdapter */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        HasAccessToSheet $hasAccessToSheet,
        QueryBusInterface $queryBus,
        FormFactoryInterface $formFactory,
        CommandBus $commandBus,
        \DateTimeInterface $dateTime,
        ContactRepositoryInterface $contactRepository,
        RouterAdapter $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->hasAccessToSheet = $hasAccessToSheet;
        $this->queryBus = $queryBus;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->dateTime = $dateTime;
        $this->contactRepository = $contactRepository;
        $this->router = $router;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        User $contactUser
    ): Response {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->hasAccessToSheet->isSatisfiedBy($user, $event, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        // if owner is logged and is not a participant, it fallback to one of the participant
        $participant = $sheet->getUserParticipant($userDomain->getUser()) ?? $sheet->getFirstParticipant();

        $contactView = $this->queryBus->handle(
            new GetContactViewQuery($event, $sheet, $participant, $contactUser, $request->getLocale())
        );

        $formView = null;
        switch ($request->query->get(self::MODE_QUERY_KEY)) {
            case self::MODE_EDIT_EVALUATION:

                $contactQuery = new Contact($event, $participant->getUser(), $contactUser, $this->dateTime);
                $contact = $this->contactRepository->find($contactQuery);

                if (null === $contact) {
                    throw new AccessDeniedException();
                }

                $editEvaluationCommand = new EditEvaluation($contact, $contact->getEvaluation());
                $form = $this->formFactory->create(EvaluationType::class, $editEvaluationCommand);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $this->commandBus->handle($editEvaluationCommand);

                    return new RedirectResponse(
                        $this->router->generate(
                            'event_contact_show',
                            ['sheet' => $sheet->getId(), 'contactUser' => $contactUser->getId()]
                        )
                    );
                }

                $formView = $form->createView();

                break;
        }

        return new Response(
            $this->engine->render(
                '@Event/Contact/show.html.twig',
                [
                    'event'       => $event,
                    'sheet'       => $sheet,
                    'contactView' => $contactView,
                    'form'        => $formView,
                ]
            )
        );
    }
}
