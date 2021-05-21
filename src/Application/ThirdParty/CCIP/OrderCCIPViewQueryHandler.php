<?php


namespace Proximum\Vimeet\Application\ThirdParty\CCIP;

use Payum\Core\Payum;
use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\MissingCcipProductId;
use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\UnsupportedMultipleRows;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Payment\PaymentToken;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
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

        $paidRows = array_filter($orderCCIPViewQuery->order->getRows(), function(Row $row){
            return $row->getPrice()>0;
        });

        if (count($paidRows) > 1) {
            throw new UnsupportedMultipleRows('Unsupported multiple rows for CCIP, order: ' . $orderCCIPViewQuery->order->getNumero());
        }

        $password = bin2hex(random_bytes(10));

        /** @var PaymentToken $paymentToken */
        $paymentToken = $this->payum->getTokenStorage()->find($orderCCIPViewQuery->captureToken);
        /** @var Payment $payment */
        $payment = $this->payum->getStorage(Payment::class)->find($paymentToken->getDetails());

        $row = $paidRows[0];

        $billingInfo = $this->billingInfoRepository->getBySheet($orderCCIPViewQuery->order->getSheet());

        $extraParameter = $this->extraParameterRepository->findByEventAndType(
            $orderCCIPViewQuery->order->getSheet()->getEvent(),
            Type::TYPE_PRODUCT_CCIP
        );

        if($extraParameter === null || $extraParameter->getValue() === null){
            throw new \Exception('Product CCIP parameter is not defined');
        }

        $parameters = json_decode($extraParameter->getValue(), true);

        $orders = $this->orderRepository->findByIds(explode(',', $orderCCIPViewQuery->transaction->getInternalReference()));

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
            $row->getProduct()->getTitle($orderCCIPViewQuery->locale),
            $row->getQuantity(),
            $row->getVatRate(),
            $row->getLabel()??'',
            $row->getPrice(),
            $password,
            $orderCCIPViewQuery->captureToken,
            $payment->getNumber()
        );
    }
}
