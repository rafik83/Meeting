<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;


use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\InvalidPaymentNumber;
use Proximum\Vimeet\Domain\Model\Transaction;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderCCIPUpdateAction
{

    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Transaction $transaction, string $paymentNumber): Response
    {
        if ($transaction->getPayment()->getNumber() !== $paymentNumber) {
            throw new InvalidPaymentNumber(
                'Invalid payment number for transaction #' . $transaction->getId() . ' while update endpoint is called'
            );
        }

        $this->logger->info(
            '[CCIP Payment] Update received transaction {transactionId}, status {status}',
            ['transactionId' => $transaction->getId(), 'status' => $request->request->get('update')]
        );

        if ($request->request->get('update') === '00') {
            $transaction->setPaid();
        }

        return new Response('Transaction validated');
    }
}
