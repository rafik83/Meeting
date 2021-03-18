<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Order;

class OrderDateSubstitution implements SubstituteInterface
{
    /**
    urlEventProForma
    urlEventProFormaWithCTA
     */
    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!method_exists($prepareMail, 'getOrder')) {
            return '';
        }

        $order = $prepareMail->getOrder();

        if (!$order instanceof Order) {
            return '';
        }

        $dateFormatter = \IntlDateFormatter::create(
            $prepareMail->locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $prepareMail->event->getTimeZone()
        );

        return $dateFormatter->format($order->getCreatedAt());
    }
}
