<?php


namespace Proximum\Vimeet\Application\ThirdParty\CCIP;

use Payum\Core\Payum;
use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\MissingCcipProductId;
use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\TransactionHasNoRelatedOrder;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Payment\PaymentToken;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use RuntimeException;
use Symfony\Component\Intl\Countries;

class OrderCCIPViewQueryHandler
{
    public const TEMPLATE_ERROR_CANCEL_URL = 'AdminBundle:ThirdParty:CCIP:orderCCIPErrorCancelUrl.html.twig';

    private BillingInfoRepositoryInterface $billingInfoRepository;
    private Payum $payum;
    private ExtraParameterRepositoryInterface $extraParameterRepository;
    private OrderRepositoryInterface $orderRepository;

    public function __construct
    (
        Payum $payum,
        BillingInfoRepositoryInterface $billingInfoRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->payum = $payum;
        $this->billingInfoRepository = $billingInfoRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->orderRepository = $orderRepository;
    }

    public function handle(OrderCCIPViewQuery $orderCCIPViewQuery): OrderCCIPView
    {

        $password = bin2hex(random_bytes(10));

        /** @var PaymentToken $paymentToken */
        $paymentToken = $this->payum->getTokenStorage()->find($orderCCIPViewQuery->captureToken);
        /** @var Payment $payment */
        $payment = $this->payum->getStorage(Payment::class)->find($paymentToken->getDetails());

        $sheet = $orderCCIPViewQuery->transaction->getSheet();
        $billingInfo = $this->billingInfoRepository->getBySheet($sheet);

        $extraParameter = $this->extraParameterRepository->findByEventAndType(
            $sheet->getEvent(),
            Type::TYPE_PRODUCT_CCIP
        );

        if ($extraParameter === null || $extraParameter->getValue() === null) {
            throw new RuntimeException(
                sprintf('Product CCIP parameter is not defined (transaction #%d)', $orderCCIPViewQuery->transaction->getId())
            );
        }

        $parameters = json_decode($extraParameter->getValue(), true);

        $orders = $this->orderRepository->findByIds(explode(',', $orderCCIPViewQuery->transaction->getInternalReference()));

        if (empty($orders)) {
            throw new TransactionHasNoRelatedOrder(
                sprintf('No order found for transaction #%d', $orderCCIPViewQuery->transaction->getId())
            );
        }

        // check that products are supported
        foreach ($orders as $order) {
            foreach ($order->getRows() as $orderRow) {
                if (!isset($parameters[$orderRow->getProductId()])) {
                    throw new MissingCcipProductId(
                        sprintf('CCIP product id not found in extra parameter for product %d', $orderRow->getProductId())
                    );
                }
            }
        }

        return new OrderCCIPView(
            $orderCCIPViewQuery->transaction->getId(),
            $orderCCIPViewQuery->transaction->getDate(),
            $sheet,
            $orders,
            $orderCCIPViewQuery->user,
            $orderCCIPViewQuery->user->getEmail(),
            $orderCCIPViewQuery->user->getGender() ? substr($orderCCIPViewQuery->user->getGender(), 0, 1) : null,
            $orderCCIPViewQuery->user->getFirstName(),
            $orderCCIPViewQuery->user->getLastName(),
            $billingInfo->getAddress()->getStreet(),
            $billingInfo->getAddress()->getZipcode(),
            $billingInfo->getAddress()->getCity(),
            Countries::getAlpha3Code($billingInfo->getAddress()->getCountry()),
            $orderCCIPViewQuery->user->getPhone()??'',
            $parameters,
            $orderCCIPViewQuery->locale,
            $password,
            $orderCCIPViewQuery->captureToken,
            $payment->getNumber()
        );
    }
}
