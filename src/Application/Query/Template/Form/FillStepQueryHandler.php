<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Template\Form;

use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Exception\BlockForGivenStepNotFoundException;


class FillStepQueryHandler
{
    /** @var FormTemplateDataQueryHandler */
    private $formTemplateDataQueryHandler;

    public function __construct(FormTemplateDataQueryHandler $formTemplateDataQueryHandler)
    {
        $this->formTemplateDataQueryHandler = $formTemplateDataQueryHandler;
    }

    /**
     * @param FillStepQuery $query
     *
     * @return BlockStepView
     *
     * @throws BlockForGivenStepNotFoundException
     */
    public function handle(FillStepQuery $query): BlockStepView
    {
        $templateData = $this->formTemplateDataQueryHandler->handle(
            new FormTemplateDataQuery(
                $query->formTemplate,
                $query->sheet,
                $query->participant,
                $query->locale
            )
        );

        $block = $templateData->getBlock($query->step);

        if (!$block instanceof Block) {
            throw new BlockForGivenStepNotFoundException($query->step);
        }

        return new BlockStepView($block, $query->step, $templateData->getBlocksCount());
    }
}
