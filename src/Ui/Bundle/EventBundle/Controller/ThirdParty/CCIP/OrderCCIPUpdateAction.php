<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;


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

    public function __invoke(Request $request, Transaction $transaction): Response
    {
        $this->logger->info(
            '[CCIP Payment] Update received transaction {transactionId}, status {status}',
            ['transactionId' => $transaction->getId(), 'status' => $request->request->get('update')]
        );

        return new Response();
    }
}
