<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Contact;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Contact\ContactListView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class IndexAction
{
    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    public function __construct(
        QueryBusInterface $queryBus,
        Environment $twig,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EventOpenAccessChecker $eventOpenAccessChecker
    ) {
        $this->twig = $twig;
        $this->queryBus = $queryBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    ) {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        // if owner is logged and is not a participant, it fallback to one of the participant
        $participant = $sheet->getUserParticipant($userDomain->getUser()) ?? $sheet->getFirstParticipant();

        $event = $eventDomain->getEvent();

        /** @var ContactListView $contactListView */
        $contactListView = $this->queryBus->handle(
            new GetContactListViewQuery($event, $participant, $request->getLocale())
        );

        $user = $userDomain->getUser();
        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_CONTACTS,
            $request->getLocale()
        );
        $tipTranslationViews = $this->queryBus->handle($tipTranslationViewQuery);

        return new Response(
            $this->twig->render(
                '@Event/Contact/index.html.twig',
                [
                    'contactListView' => $contactListView,
                    'sheet' => $sheet,
                    'event' => $event,
                    'isEventOpen' => $this->eventOpenAccessChecker->allowedToAccess($event),
                    'tipTranslationViews' => $tipTranslationViews,
                ]
            )
        );
    }
}
