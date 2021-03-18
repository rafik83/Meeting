<?php

namespace Proximum\Vimeet\Application\Query\Badge\QRCode;

class QRCodeIdentifierQueryHandler
{
    public const USER_PAD_LEFT = 7;
    public const EVENT_PAD_LEFT = 5;

    public function handle(QRCodeIdentifierQuery $query): string
    {
        return sprintf(
            '%s%s',
            str_pad((string) $query->user->getId(), self::USER_PAD_LEFT, '0', STR_PAD_LEFT),
            str_pad((string) $query->event->getId(), self::EVENT_PAD_LEFT, '0', STR_PAD_LEFT)
        );
    }
}
