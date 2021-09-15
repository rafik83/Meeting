<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link;

use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Order;

class EventProFormaLinkSubstitution implements SubstituteInterface
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(EventUrlGeneratorInterface $eventUrlGenerator)
    {
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail->hasSheet() || !method_exists($prepareMail, 'getOrder')) {
            return '';
        }

        $order = $prepareMail->getOrder();

        if (!$order instanceof Order) {
            return '';
        }

        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $prepareMail->event,
            Route::ORDER_PRO_FORMA,
            [
                'sheet' => $prepareMail->sheet->getId(),
                'order' => $order->getId(),
                '_locale' => $prepareMail->event->getAvailableLocale($prepareMail->locale),
            ]
        );
    }
}
