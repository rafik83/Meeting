<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Contact;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Contact\EditComment;
use Proximum\Vimeet\Application\Command\Contact\EditEvaluation;
use Proximum\Vimeet\Application\Query\Contact\ContactView;
use Proximum\Vimeet\Application\Query\Contact\GetContactViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetMeViewQuery;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\User\Sheet\HasAccessToSheet;
use Proximum\Vimeet\Infrastructure\Adapter\CommandBus;
use Proximum\Vimeet\Infrastructure\Adapter\RouterAdapter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Contact\CommentType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Contact\EvaluationType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ShowAction
{
    public const MODE_QUERY_KEY = 'mode';
    public const MODE_EDIT_EVALUATION = 'evaluation';
    public const MODE_EDIT_COMMENT = 'comment';
    public const MODE_VIEW = 'view';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var Environment */
    private $twig;

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
        Environment $twig,
        HasAccessToSheet $hasAccessToSheet,
        QueryBusInterface $queryBus,
        FormFactoryInterface $formFactory,
        CommandBus $commandBus,
        \DateTimeInterface $dateTime,
        ContactRepositoryInterface $contactRepository,
        RouterAdapter $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->twig = $twig;
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

        if ($user === $contactUser) {
            /** @var ContactView $contactView */
            $contactView = $this->queryBus->handle(
                new GetMeViewQuery(
                    $event,
                    $sheet->getUserParticipant($user) ?? $sheet->getFirstParticipant(),
                    $request->getLocale()
                )
            );

            return new Response(
                $this->twig->render(
                    '@Event/Contact/me.html.twig',
                    [
                        'event' => $event,
                        'sheet' => $sheet,
                        'contactView' => $contactView,
                    ]
                )
            );
        }

        $mode = $request->query->get(self::MODE_QUERY_KEY, self::MODE_VIEW);
        if (!\in_array($mode, [self::MODE_EDIT_COMMENT, self::MODE_EDIT_EVALUATION, self::MODE_VIEW], true)) {
            throw new AccessDeniedException();
        }

        // if owner is logged and is not a participant, it fallback to one of the participant
        $participant = $sheet->getUserParticipant($user) ?? $sheet->getFirstParticipant();

        /** @var ContactView $contactView */
        $contactView = $this->queryBus->handle(
            new GetContactViewQuery($event, $sheet, $participant, $contactUser, $request->getLocale())
        );

        $contact = $this->getContact($contactUser, $event, $participant);

        /** @var FormInterface $ratingForm */
        /** @var EditEvaluation $editEvaluationCommand */
        [$editEvaluationCommand, $ratingForm] = $this->prepareRatingForm($request, $sheet, $contact, $mode);

        if ($ratingForm->isSubmitted() && $ratingForm->isValid()) {
            $this->commandBus->handle($editEvaluationCommand);

            return new RedirectResponse(
                $this->router->generate(
                    'event_contact_show',
                    ['sheet' => $sheet->getId(), 'contactUser' => $contactUser->getId(), self::MODE_QUERY_KEY => $mode]
                )
            );
        }

        $commentFormView = null;
        if ($mode === self::MODE_EDIT_COMMENT) {
            /** @var FormInterface $commentForm */
            /** @var EditComment $editCommentCommand */
            [$editCommentCommand, $commentForm] = $this->prepareCommentForm($request, $contact);

            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $this->commandBus->handle($editCommentCommand);

                return new RedirectResponse(
                    $this->router->generate(
                        'event_contact_show',
                        ['sheet' => $sheet->getId(), 'contactUser' => $contactUser->getId()]
                    )
                );
            }
            $commentFormView = $commentForm->createView();
        }

        return new Response(
            $this->twig->render(
                '@Event/Contact/show.html.twig',
                [
                    'event'       => $event,
                    'sheet'       => $sheet,
                    'contactView' => $contactView,
                    'ratingForm'  => $ratingForm->createView(),
                    'commentForm' => $commentFormView,
                    'mode'        => $mode,
                ]
            )
        );
    }

    protected function getContact(User $contactUser, Event $event, Participant $participant): Contact
    {
        $contactQuery = new Contact($event, $participant->getUser(), $contactUser, $this->dateTime, Contact::ORIGIN_NONE);

        return $this->contactRepository->find($contactQuery);
    }


    protected function prepareRatingForm(Request $request, Sheet $sheet, Contact $contact, string $mode): array
    {
        $options = [];

        if ($mode === self::MODE_EDIT_EVALUATION) {
            $options = [
                'action' => $this->router->generate(
                    'event_contact_show',
                    [
                        'sheet'              => $sheet->getId(),
                        'contactUser'        => $contact->getContact()->getId(),
                        self::MODE_QUERY_KEY => self::MODE_EDIT_COMMENT,
                    ]
                ),
            ];
        }

        $editEvaluationCommand = new EditEvaluation($contact, $contact->getEvaluation(), $sheet, $this->dateTime);
        $ratingForm = $this->formFactory->create(EvaluationType::class, $editEvaluationCommand, $options);

        $ratingForm->handleRequest($request);

        return [$editEvaluationCommand, $ratingForm];
    }

    protected function prepareCommentForm(Request $request, Contact $contact): array
    {
        $editEvaluationCommand = new EditComment($contact, $contact->getComment());
        $ratingForm = $this->formFactory->create(CommentType::class, $editEvaluationCommand);

        $ratingForm->handleRequest($request);

        return [$editEvaluationCommand, $ratingForm];
    }
}
