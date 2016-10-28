<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\View\Sheet\SheetValidationView;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetCompletenessRepositoryInterface;

class SheetValidationViewQueryHandler
{
    const SHEET_COMPLETE_MESSAGE   = 'sheet.validation.complete.message';
    const SHEET_UNCOMPLETE_MESSAGE = 'sheet.validation.uncomplete.message';

    /**
     * @var SheetCompletenessRepositoryInterface
     */
    private $sheetCompletenessRepository;

    /**
     * SheetValidationViewQueryHandler constructor.
     *
     * @param SheetCompletenessRepositoryInterface $sheetCompletenessRepository
     */
    public function __construct(SheetCompletenessRepositoryInterface $sheetCompletenessRepository)
    {
        $this->sheetCompletenessRepository = $sheetCompletenessRepository;
    }

    /**
     * @param SheetValidationViewQuery $query
     *
     * @return SheetValidationView
     */
    public function handle(SheetValidationViewQuery $query)
    {
        $sheetCompleteness = $this->sheetCompletenessRepository->findCompleteness(
            $query->sheet,
            $query->locale
        );

        return new SheetValidationView(
            $query->sheet,
            self::SHEET_COMPLETE_MESSAGE,
            self::SHEET_UNCOMPLETE_MESSAGE,
            ($sheetCompleteness !== null) ? $sheetCompleteness->getCompleteness() : 0
        );
    }
}
