<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\StaticFormulation;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\StaticFormulation\StaticFormulationListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    /** @var Environment */
    private $twig;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        Environment $twig
    ) {
        $this->twig = $twig;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $list = $this->queryBus->handle(new StaticFormulationListViewQuery($event, $request));

        return new Response($this->twig->render('AdminBundle:StaticFormulation:list.html.twig', [
            'event' => $event,
            'list' => $list,
        ]));
    }
}
