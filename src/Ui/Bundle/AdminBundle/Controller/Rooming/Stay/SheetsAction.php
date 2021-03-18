<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Stay;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SheetsAction
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Event $event): JsonResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $sheets = $this->sheetRepository->getByEventAndOrderedByTitle($event);

        return new JsonResponse(
            [
                'sheets' => $sheets,
            ]
        );
    }
}
