<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Transaction;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;

class TransactionListViewHandler
{
    /**
     * @var SerializerAdapter
     */
    private $serializer;
    
    /**
     * TransactionListViewHandler constructor.
     *
     * @param SerializerAdapter $serializer
     */
    public function __construct(SerializerAdapter $serializer)
    {
        $this->serializer = $serializer;
    }
    
    /**
     * @param TransactionListView $query
     */
    public function handle(TransactionListView $query)
    {
    
    }
}
