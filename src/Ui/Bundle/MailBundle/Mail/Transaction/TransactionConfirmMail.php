<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Transaction;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;

class TransactionConfirmMail extends Mail
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * TransactionConfirmEvent constructor.
     *
     * @param string      $sender
     * @param string      $receiver
     * @param string      $template
     * @param string      $messageId
     * @param string      $locale
     * @param User        $user
     * @param Transaction $transaction
     */
    public function __construct(
        $sender,
        $receiver,
        $template,
        $messageId,
        $locale,
        User $user,
        Transaction $transaction
    ) {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);

        $this->user        = $user;
        $this->transaction = $transaction;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
