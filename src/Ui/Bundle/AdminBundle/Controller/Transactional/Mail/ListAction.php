<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Transactional\Mail;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Transactional\Mail\TransactionalMailListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
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

    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->queryBus = $queryBus;
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $list = $this->queryBus->handle(new TransactionalMailListViewQuery($event, $request->getLocale()));

        return $this->engine->renderResponse('AdminBundle:Transactional/Mail:list.html.twig', [
            'event' => $event,
            'list' => $list,
        ]);
    }
}
