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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;

class TransactionConfirmMail extends Mail
{
    /**
     * @var string
     */
    protected $subject = 'mail.transaction.confirm.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Transaction/transactionConfirm.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::TRANSACTION_CONFIRMED;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = true;

    /**
     * TransactionConfirmEvent constructor.
     *
     * @param Transaction $transaction
     * @param User        $user
     * @param string      $sender
     * @param string      $receiver
     * @param string      $locale
     */
    public function __construct(
        Transaction $transaction,
        User $user,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale, null, null, $transaction->getSheet()->getEvent());

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

    /**
     * {@inheritdoc}
     */
    public function getSubjectParameters()
    {
        return [
            '%event%' => $this->getEvent()->getTitle(),
        ];
    }
}
