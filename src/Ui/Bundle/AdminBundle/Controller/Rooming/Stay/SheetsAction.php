<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

        $sheetsDTO = [];
        foreach ($sheets as $sheet) {
            $sheetsDTO[] = ['id' => $sheet->getId(), 'title' => $sheet->getTitle()];
        }

        return new JsonResponse(
            [
                'sheets' => $sheetsDTO,
            ]
        );
    }
}
