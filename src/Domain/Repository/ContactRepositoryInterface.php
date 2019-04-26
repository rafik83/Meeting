<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Contact;

interface ContactRepositoryInterface
{
    public function add(Contact $contact): void;

    public function find(Contact $contact): ?Contact;
}
