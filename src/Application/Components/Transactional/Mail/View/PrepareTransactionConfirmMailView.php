<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareTransactionConfirmMailView extends AbstractPrepareMail
{
    /** @var Transaction */
    public $transaction;

    public function __construct(
        Event $event,
        User $user,
        string $locale,
        Sheet $sheet,
        Transaction $transaction
    ) {
        parent::__construct(
            $event,
            $user,
            Constant::TRANSACTIONAL_MAIL_KEY_TRANSACTION_CONFIRMED,
            $locale,
            $sheet
        );

        $this->transaction = $transaction;
    }
}
