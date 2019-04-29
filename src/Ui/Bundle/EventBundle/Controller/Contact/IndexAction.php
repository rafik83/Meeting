<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Contact;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Contact\ContactPreviewView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListViewQuery;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class IndexAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EventOpenAccessChecker $eventOpenAccessChecker
    ) {
        $this->engine = $engine;
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

        $participant = $sheet->getUserParticipant($userDomain->getUser());

        /** @var ContactPreviewView[] $contactListView */
        $contactListView = $this->queryBus->handle(
            new GetContactListViewQuery($eventDomain->getEvent(), $participant, $request->getLocale())
        );

        $event = $eventDomain->getEvent();

        return new Response(
            $this->engine->render(
                '@Event/Contact/index.html.twig',
                [
                    'contactListView' => $contactListView,
                    'sheet' => $sheet,
                    'event' => $event,
                    'isEventOpen' => $this->eventOpenAccessChecker->allowedToAccess($event),
                ]
            )
        );
    }
}
