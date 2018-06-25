<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Badge\QRCode;

class QRCodeIdentifierQueryHandler
{
    public function handle(QRCodeIdentifierQuery $query): string
    {
        return sprintf(
            '%s%s',
            str_pad((string) $query->user->getId(), 7, '0', STR_PAD_LEFT),
            str_pad((string) $query->event->getId(), 5, '0', STR_PAD_LEFT)
        );
    }
}
