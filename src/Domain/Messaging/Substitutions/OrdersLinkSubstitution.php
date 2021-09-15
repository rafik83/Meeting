<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Adapter\EventUrlGenerator;

class OrdersLinkSubstitution implements SubstituteInterface
{
    /**
     * @var EventUrlGenerator
     */
    private $eventUrlGenerator;

    /**
     * OrdersLinkSubstitution constructor.
     *
     * @param EventUrlGeneratorInterface $eventUrlGenerator
     */
    public function __construct(EventUrlGeneratorInterface $eventUrlGenerator)
    {
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        $ordersUrl = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $sheet->getEvent(),
            'event_order_list',
            [
                '_locale' => $sheet->getOwnerLocale(),
                'sheet'   => $sheet->getId(),
            ]
        );

        return $ordersUrl;
    }
}
