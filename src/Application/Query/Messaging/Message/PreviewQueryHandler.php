<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
        return new PreviewView(
            $query->message->getEvent(),
            $query->locale,
            $query->message->getSubject($query->locale),
            $query->message->getContent($query->locale)
        );
    }
}
