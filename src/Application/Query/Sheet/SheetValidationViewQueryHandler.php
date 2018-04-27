<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\View\Sheet\SheetValidationView;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetCompletenessRepositoryInterface;

class SheetValidationViewQueryHandler
{
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
            (null !== $sheetCompleteness) ? $sheetCompleteness->getCompleteness() : 0
        );
    }
}
