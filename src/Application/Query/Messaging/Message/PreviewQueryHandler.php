<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Messaging\Message;

class PreviewQueryHandler
{
    /**
     * @param PreviewQuery $query
     *
     * @return PreviewView
     */
    public function handle(PreviewQuery $query)
    {
        return PreviewView::createFromMessage($query->getMessage());
    }
}
