<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Adapter\IntlInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareTransactionConfirmMailView;

class TransactionTotalSubstitution implements SubstituteInterface
{
    /** @var IntlInterface */
    private $intl;

    public function __construct(IntlInterface $intl)
    {
        $this->intl = $intl;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail instanceof PrepareTransactionConfirmMailView) {
            return '';
        }

        return sprintf(
            '%.2f %s',
            $prepareMail->transaction->getAmount(),
            $this->intl->currencySymbol($prepareMail->transaction->getCurrency(), $prepareMail->locale)
        );
    }
}
