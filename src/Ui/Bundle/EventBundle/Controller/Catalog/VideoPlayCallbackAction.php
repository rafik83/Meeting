<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\AddClick;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;

class VideoPlayCallbackAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
    }

    public function __invoke(
        Sheet $sheet,
        string $objectId,
        ?UserDomain $userDomain
    ): JsonResponse {
        if (
            !$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            return new JsonResponse([
                'status' => 'anonymous',
            ]);
        }

        $user = $userDomain->getUser();

        $addClick = new AddClick($user, $sheet, $objectId, null);
        $this->commandBus->handle($addClick);

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }
}
