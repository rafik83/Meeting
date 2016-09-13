<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\CatalogSheetPreviewView;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Composer;

class CatalogSheetPreviewViewQueryHandler
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var Preview
     */
    private $preview;

    /**
     * @var Composer
     */
    private $ruleComposer;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @param SheetInfoGuesser        $sheetInfoGuesser
     * @param Composer                $ruleComposer
     * @param Preview                 $preview
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        Composer $ruleComposer,
        Preview $preview,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->ruleComposer     = $ruleComposer;
        $this->preview          = $preview;
        $this->ruleRepository   = $ruleRepository;
    }

    /**
     * @param CatalogSheetPreviewViewQuery $catalogSheetPreviewViewQuery
     *
     * @return CatalogSheetPreviewView
     */
    public function handle(CatalogSheetPreviewViewQuery $catalogSheetPreviewViewQuery)
    {
        $viewer = $catalogSheetPreviewViewQuery->viewer;
        $sheet  = $catalogSheetPreviewViewQuery->sheet;
        $locale = $catalogSheetPreviewViewQuery->locale;
        $rules  = $this->ruleRepository->getBySeerTypeAndSeeableType($viewer->getType(), $sheet->getType());

        if (!empty($rules)) {
            $rule = $this->ruleComposer->compose($rules);
        } else {
            $rule = null;
        }

        return new CatalogSheetPreviewView(
            $sheet->getId(),
            $this->sheetInfoGuesser->guessSheetName($sheet, $locale),
            $sheet->getType()->getTitle($locale),
            $this->preview->getPreview($sheet, $locale, $rule)
        );
    }
}
