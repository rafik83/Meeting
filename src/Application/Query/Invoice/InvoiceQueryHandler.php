<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Invoice;

use Proximum\Vimeet\Application\View\Invoice\InvoiceView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;

class InvoiceQueryHandler
{
    /**
     * @var SerializerAdapter
     */
    private $serializer;

    /**
     * @param SerializerAdapter $serializer
     */
    public function __construct(SerializerAdapter $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param InvoiceQuery $invoiceQuery
     *
     * @return InvoiceView
     */
    public function handle(InvoiceQuery $invoiceQuery)
    {
        return $this->serializer->deserialize(
            $invoiceQuery->invoice->getData(),
            InvoiceView::class,
            'json',
            ['invoice' => $invoiceQuery->invoice]
        );
    }
}
