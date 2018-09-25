<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Transactional\Mail\Generic;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Transactional\Mail\Generic\GenericMailView;

class GenericMailViewQueryHandler
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function handle(GenericMailViewQuery $query): GenericMailView
    {
        return new GenericMailView(
            $query->key,
            $this->translator->trans($query->data['subject'], [], 'mail', $query->locale)
        );
    }
}
