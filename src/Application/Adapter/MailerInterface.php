<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;

interface MailerInterface
{
    /**
     * @param AbstractMail $mail
     */
    public function send(AbstractMail $mail);
}
