<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Transactional\Mail;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Transactional\Mail\TransactionalMailListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var Environment */
    private $twig;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        QueryBusInterface $queryBus,
        Environment $twig,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->queryBus = $queryBus;
        $this->twig = $twig;
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

        return new Response($this->twig->render('AdminBundle:Transactional/Mail:list.html.twig', [
            'event' => $event,
            'list' => $list,
        ]));
    }
}
