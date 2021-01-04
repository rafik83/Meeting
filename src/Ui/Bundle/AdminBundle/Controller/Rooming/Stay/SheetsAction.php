<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class SheetsAction
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    public function __invoke(Event $event): JsonResponse
    {
        $sheets = $this->sheetRepository->getByEventAndOrderedByTitle($event);

        return new JsonResponse(
            [
                'sheets' => $sheets,
            ]
        );
    }
}
