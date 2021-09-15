<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmation;
use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmationHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IgnoreConfirmationAction
{
    /** @var IgnoreConfirmationHandler */
    private $ignoreConfirmationHandler;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /**
     * @param IgnoreConfirmationHandler            $ignoreConfirmationHandler
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     */
    public function __construct(
        IgnoreConfirmationHandler $ignoreConfirmationHandler,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->ignoreConfirmationHandler = $ignoreConfirmationHandler;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
    public function __invoke(Sheet $sheet, Participant $participant): JsonResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException('Access denied');
        }

        $this->ignoreConfirmationHandler->handle(
            new IgnoreConfirmation(
                $sheet->getEvent(),
                $participant
            )
        );

        return new JsonResponse([]);
    }
}
