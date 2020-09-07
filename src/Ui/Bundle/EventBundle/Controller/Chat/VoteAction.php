<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Chat;


use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;

class VoteAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }
}
