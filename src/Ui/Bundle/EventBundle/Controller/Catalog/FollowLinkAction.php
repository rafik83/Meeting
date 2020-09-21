<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\AddClick;
use Proximum\Vimeet\Application\Query\Sheet\Template\TemplateObjectUrlQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class FollowLinkAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function __invoke(
        Sheet $sheet,
        string $objectId,
        ?int $index = null,
        EventDomain $eventDomain,
        UserDomain $userDomain
    ): RedirectResponse {
        if (
            !$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            throw new AccessDeniedHttpException('Access denied to this sheet');
        }

        $user = $userDomain->getUser();
        $event = $eventDomain->getEvent();

        $url = $this->queryBus->handle(new TemplateObjectUrlQuery(
            $sheet,
            $event,
            $user->getLocale(),
            $objectId,
            $index
        ));

        $addClick = new AddClick($user, $sheet, $objectId, $index);
        $this->commandBus->handle($addClick);

        return new RedirectResponse($url);
    }
}
