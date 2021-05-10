<?php


namespace Proximum\Vimeet\Application\ThirdParty\CCIP;


use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\UnsupportedMultipleRows;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Symfony\Component\Intl\Countries;
use Twig\Environment;

class OrderCCIPViewQueryHandler
{
    public const TEMPLATE_ERROR_CANCEL_URL = 'AdminBundle:ThirdParty:CCIP:orderCCIPErrorCancelUrl.html.twig';

    private Environment $twig;

    private BillingInfoRepositoryInterface $billingInfoRepository;

    public function __construct
    (
        Environment $twig,
        BillingInfoRepositoryInterface $billingInfoRepository
    )
    {
        $this->twig = $twig;
        $this->billingInfoRepository = $billingInfoRepository;
    }

    public function handle(OrderCCIPViewQuery $orderCCIPViewQuery)
    {

        $paidRows = array_filter($orderCCIPViewQuery->order->getRows(), function(Row $row){
            return $row->getPrice()>0;
        });

        if (count($paidRows) > 1) {
            throw new UnsupportedMultipleRows('Unsupported multiple rows for CCIP, order: ' . $orderCCIPViewQuery->order->getNumero());
        }

        // si la commande est annulée on envoie vers le twig avec le message annulé
        // return new Response($this->twig->render(self::TEMPLATE_ERROR_CANCEL_URL, ['cancel']));

        // si la commande est en erreur on envoie vers le twig avec le message erreur
        // return new Response($this->twig->render(self::TEMPLATE_ERROR_CANCEL_URL, ['error']));

        $password = bin2hex(random_bytes(10));

        $row = $paidRows[0];

        $billingInfo = $this->billingInfoRepository->getBySheet($orderCCIPViewQuery->order->getSheet());

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
            $row->getProductId(),
            $row->getProduct()->getTitle($orderCCIPViewQuery->locale),
            $row->getQuantity(),
            $row->getVatRate(),
            $row->getLabel()??'',
            $row->getPrice(),
            $password
        );
    }
}
