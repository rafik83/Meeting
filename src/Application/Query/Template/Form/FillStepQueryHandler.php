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
use Proximum\Vimeet\Domain\Template\Exception\GivenStepIsRequiredAndNotFilledException;

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

        if ($query->step !== 1) {
            foreach ($templateData->getBlocks() as $level => $block) {
                if (($level + 1) === $query->step) {
                    break;
                }

                foreach ($block->getEditableObjects() as $object) {
                    if (true === $object->getRequired() && true === $object->isEmpty()) {
                        throw new GivenStepIsRequiredAndNotFilledException($level + 1);
                    }
                }
            }
        }

        return new BlockStepView($block, $query->step, $templateData->getBlocksCount());
    }
}
