<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Block;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Components\Template\TemplateFactory;
use Proximum\Vimeet\Domain\Model\Sheet;

class BlockDataViewFactory
{
    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return BlockDataView[]
     */
    public function createBlockViews(Sheet $sheet, $locale)
    {
        $blocksViews = [];
        $data        = new ArrayCollection($sheet->getData());
        $templates   = (new TemplateFactory())->createTemplatesFromArray($sheet->getType()->getSheetTemplate());

        foreach ($templates->getTemplates() as $templateKey => $template) {
            $rowViews  = [];
            $blockData = new ArrayCollection($data->containsKey($templateKey) ? $data->get($templateKey) : []);

            foreach ($template->getRows() as $rowKey => $row) {

                // Don't add private data
                if ($row->isPrivate()) {
                    continue;
                }

                $rowViews[$rowKey] = new RowDataView(
                    $row->getLabel($locale),
                    $row->getDisplayableValue($blockData->get($rowKey), $locale) ? : '...'
                );
            }

            $blocksViews[$templateKey] = new BlockDataView($template->getLabel($locale), $rowViews);
        }

        return $blocksViews;
    }
}
