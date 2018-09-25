<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Transactional\Mail;

use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQuery;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQueryHandler;
use Proximum\Vimeet\Application\View\Transactional\Mail\TransactionalMailListView;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class TransactionalMailListViewQueryHandler
{
    /** @var GenericMailViewQueryHandler */
    private $genericMailViewQueryHandler;

    public function __construct(GenericMailViewQueryHandler $genericMailViewQueryHandler)
    {
        $this->genericMailViewQueryHandler = $genericMailViewQueryHandler;
    }

    public function handle(TransactionalMailListViewQuery $query): TransactionalMailListView
    {
        $locale = $query->event->getAvailableLocale($query->locale);

        $generics = [];

        foreach (Constant::TRANSACTIONAL_MAIL_LIST as $key => $data) {
            $generics[] = $this->genericMailViewQueryHandler->handle(new GenericMailViewQuery(
                $locale,
                $key,
                $data
            ));
        }

        return new TransactionalMailListView($generics);
    }
}
