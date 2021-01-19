<?php

namespace Proximum\Vimeet\Domain\Order\Numero;

use Proximum\Vimeet\Domain\Model\Order;

class Generator
{
    /**
     * @param Order $order
     *
     * @return string
     */
    public static function generate(Order $order): string
    {
        return sprintf(
            '%s-%s-%s',
            str_pad($order->getSheet()->getEvent()->getId(), 2, '0', STR_PAD_LEFT),
            str_pad($order->getSheet()->getId(), 2, '0', STR_PAD_LEFT),
            str_pad($order->getId(), 2, '0', STR_PAD_LEFT)
        );
    }
}
