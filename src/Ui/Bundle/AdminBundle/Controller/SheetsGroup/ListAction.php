<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\SheetsGroup;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\GroupListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    const TEMPLATE = 'AdminBundle:SheetsGroup:list.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var Environment */
    private $twig;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->twig = $twig;
    }

    public function __invoke(Event $event, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_OPERATE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $groupViews = $this->queryBus->handle(new GroupListViewQuery($event, $adminDomain->getAdmin()));

        return new Response($this->twig->render(self::TEMPLATE, [
            'event' => $event,
            'groupViews' => $groupViews,
        ]));
    }
}
