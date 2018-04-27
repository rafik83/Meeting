<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

interface MailRecipientInterface
{
    /**
     * @return string
     */
    public function getFullname();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getLocale();
}
