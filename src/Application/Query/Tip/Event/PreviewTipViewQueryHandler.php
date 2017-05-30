<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Application\View\Tip\Event\PreviewTipView;

class PreviewTipViewQueryHandler
{
    /**
     * @param PreviewTipViewQuery $query
     * 
     * @return PreviewTipView
     */
    public function handle(PreviewTipViewQuery $query)
    {
        return new PreviewTipView(
            $query->tipTranslation->getTitle(),
            $query->tipTranslation->getContent()
        );
    }
}
