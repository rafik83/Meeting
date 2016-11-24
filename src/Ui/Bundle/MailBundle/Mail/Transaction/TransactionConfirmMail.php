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
use Proximum\Vimeet\Application\Components\Mail\ParticipantMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;

class TransactionConfirmMail extends Mail implements ParticipantMail
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
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var Participant
     */
    protected $participant;

    /**
     * TransactionConfirmEvent constructor.
     *
     * @param Transaction $transaction
     * @param User        $user
     * @param string      $sender
     * @param string      $receiver
     * @param string      $locale
     * @param Participant $participant
     * @param string      $firstname
     * @param string      $lastname
     */
    public function __construct(
        Transaction $transaction,
        User $user,
        $sender,
        $receiver,
        $locale,
        Participant $participant,
        $firstname,
        $lastname
    ) {
        parent::__construct($sender, $receiver, $locale, null, null, $transaction->getSheet()->getEvent());

        $this->user        = $user;
        $this->transaction = $transaction;
        $this->firstname   = $firstname;
        $this->lastname    = $lastname;
        $this->participant = $participant;
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

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantType()
    {
        return $this->participant->getSheet()->getType()->getTitle($this->locale);
    }
}
