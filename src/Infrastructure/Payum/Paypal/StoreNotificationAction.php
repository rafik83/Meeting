<?php

namespace Proximum\Vimeet\Infrastructure\Payum\Paypal;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Proximum\Vimeet\Domain\Model\Payment\Notification;
use Proximum\Vimeet\Domain\Repository\Payment\NotificationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Payment\PaymentRepositoryInterface;
use Proximum\Vimeet\Domain\Transaction\TransactionManager;

class StoreNotificationAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @var \DateTimeInterface
     */
    private $now;

    /**
     * @param NotificationRepositoryInterface $notificationRepository
     * @param PaymentRepositoryInterface      $paymentRepository
     * @param TransactionManager              $transactionManager
     * @param \DateTimeInterface              $now
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        PaymentRepositoryInterface $paymentRepository,
        TransactionManager $transactionManager,
        \DateTimeInterface $now
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->paymentRepository      = $paymentRepository;
        $this->transactionManager     = $transactionManager;
        $this->now                    = $now;
    }

    /**
     * @param Notify $notify
     */
    public function execute($notify)
    {
        $token = $notify->getToken();

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        $request = $getHttpRequest->request;

        $notification = new Notification($token->getGatewayName(), $request, $this->now);
        $this->notificationRepository->add($notification);

        if (isset($request['payment_status']) && Api::PAYMENTSTATUS_COMPLETED === $request['payment_status']) {
            $details     = $token->getDetails();
            $paymentId   = $details->getId();
            $payment     = $this->paymentRepository->findById($paymentId);
            $transaction = $payment->getTransaction();

            if (null !== $transaction) {
                $this->transactionManager->setPaid($transaction);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof Notify;
    }
}
