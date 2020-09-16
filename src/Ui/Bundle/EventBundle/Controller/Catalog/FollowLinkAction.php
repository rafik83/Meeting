<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\AddClick;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FollowLinkAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        SheetInfoGuesser $sheetInfoGuesser,
        LoggerInterface $logger
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->logger = $logger;
    }

    public function __invoke(
        Sheet $sheet,
        string $objectId,
        ?int $index = null,
        UserDomain $userDomain
    ): RedirectResponse {
        if (
            !$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            throw new AccessDeniedHttpException('Access denied to this sheet');
        }

        $user = $userDomain->getUser();
        if (null === $index) {
            $registrationData = $sheet->getRegistrationData();
            if (!isset($registrationData[$objectId])) {
                $this->logger->error(sprintf('ObjectId %s not found in sheet #%d registration data', $objectId, $sheet->getId()));
                throw new NotFoundHttpException('Url to redirect to not found');
            }
            $url = $registrationData[$objectId]['url'];
        } else {
            $data = $sheet->getData();
            if (!isset($data[$objectId])) {
                $this->logger->error(sprintf('ObjectId %s not found in sheet #%d registration data', $objectId, $sheet->getId()));
                throw new NotFoundHttpException('Url to redirect to not found');
            }
            $url = $data[$objectId]['medias'][$index]['url'];

        }

        $addClick = new AddClick($user, $sheet, $objectId, $index);
        $this->commandBus->handle($addClick);

        return new RedirectResponse($url);
    }
}
