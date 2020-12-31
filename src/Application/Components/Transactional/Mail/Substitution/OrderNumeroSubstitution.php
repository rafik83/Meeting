<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Order;

class OrderNumeroSubstitution implements SubstituteInterface
{
    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!method_exists($prepareMail, 'getOrder')) {
            return '';
        }

        /** @var Order $order */
        $order = $prepareMail->getOrder();

        if (!$order instanceof Order) {
            return '';
        }

        return $order->getNumero();
    }
}
