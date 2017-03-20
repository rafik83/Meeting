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

class TransactionListViewQueryHandler
{
    /**
     * @var SerializerAdapter
     */
    private $serializer;
    
    /**
     * TransactionListViewQueryHandler constructor.
     *
     * @param SerializerAdapter $serializer
     */
    public function __construct(SerializerAdapter $serializer)
    {
        $this->serializer = $serializer;
    }
    
    /**
     * @param TransactionListViewQuery $query
     *
     * @return string
     */
    public function handle(TransactionListViewQuery $query)
    {
        return $this->serializer->serialize($query, 'csv');
    }
}
