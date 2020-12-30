<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Accommodation;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Rooming\Accommodation\AccommodationListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ListAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->engine = $engine;
    }

    public function __invoke(Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $accommodationListView = $this->queryBus->handle(new AccommodationListViewQuery($event));

        return new Response($this->engine->render('AdminBundle:Rooming/Accommodation:list.html.twig', [
            'event' => $event,
            'accommodationListView' => $accommodationListView,
        ]));
    }
}
