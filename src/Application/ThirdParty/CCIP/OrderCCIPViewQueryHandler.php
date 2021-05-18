<?php


namespace Proximum\Vimeet\Application\ThirdParty\CCIP;

use Payum\Core\Payum;
use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\UnsupportedMultipleRows;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Payment\PaymentToken;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Symfony\Component\Intl\Countries;

class OrderCCIPViewQueryHandler
{
    public const TEMPLATE_ERROR_CANCEL_URL = 'AdminBundle:ThirdParty:CCIP:orderCCIPErrorCancelUrl.html.twig';

    private BillingInfoRepositoryInterface $billingInfoRepository;
    private Payum $payum;
    private ExtraParameterRepositoryInterface $extraParameterRepository;

    public function __construct
    (
        Payum $payum,
        BillingInfoRepositoryInterface $billingInfoRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->payum = $payum;
        $this->billingInfoRepository = $billingInfoRepository;
        $this->extraParameterRepository = $extraParameterRepository;
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

        return new OrderCCIPView(
            $orderCCIPViewQuery->order,
            $orderCCIPViewQuery->user,
            $orderCCIPViewQuery->user->getEmail(),
            substr($orderCCIPViewQuery->user->getGender(),0,1),
            $orderCCIPViewQuery->user->getFirstName(),
            $orderCCIPViewQuery->user->getLastName(),
            $billingInfo->getAddress()->getStreet(),
            $billingInfo->getAddress()->getZipcode(),
            $billingInfo->getAddress()->getCity(),
            Countries::getAlpha3Code($billingInfo->getAddress()->getCountry()),
            $orderCCIPViewQuery->user->getPhone()??'',
            $parameters[$row->getProductId()],
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
