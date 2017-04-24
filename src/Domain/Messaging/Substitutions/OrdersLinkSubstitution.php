<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Adapter\EventUrlGenerator;

class OrdersLinkSubstitution implements Substitute
{
    /**
     * @var EventUrlGenerator
     */
    private $eventUrlGenerator;

    /**
     * OrdersLinkSubstitution constructor.
     *
     * @param EventUrlGenerator $eventUrlGenerator
     */
    public function __construct(EventUrlGenerator $eventUrlGenerator)
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
                '_locale' => $sheet->getOwnerLocale(), 'sheet' => $sheet->getId(),
            ]
        );

        return $ordersUrl;
    }
}
