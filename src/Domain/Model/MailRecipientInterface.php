<?php

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
