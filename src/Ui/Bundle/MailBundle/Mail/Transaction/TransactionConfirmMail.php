<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Transaction;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;

class TransactionConfirmMail extends UserMail
{
    public const SUBJECT = 'mail.transaction.confirm.subject';
    public const TEMPLATE = 'MailBundle:Mail:Transaction/transactionConfirm.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Transaction/transactionConfirm_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

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
     * @param Transaction         $transaction
     * @param User                $user
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        Transaction $transaction,
        User $user,
        $sender,
        $receiver,
        $locale,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct($sender, $receiver, $locale, $transaction->getSheet()->getEvent(), $participantInfoView);

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
