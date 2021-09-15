<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\IntlInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\TransactionTotalSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareTransactionConfirmMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;

class TransactionTotalSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $transaction = $this->prophesize(Transaction::class);

        $mail = new PrepareTransactionConfirmMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $transaction->reveal()
        );
        $transaction->getAmount()->shouldBeCalled()->willReturn(12.12);
        $transaction->getCurrency()->willReturn('EUR');


        $intl = $this->prophesize(IntlInterface::class);
        $intl->currencySymbol('EUR', 'fr')->shouldBeCalled()->willReturn('€');

        $substitute = new TransactionTotalSubstitution($intl->reveal());
        $result = $substitute->substitute($mail);

        $this->assertEquals('12.12 €', $result);
    }
}
