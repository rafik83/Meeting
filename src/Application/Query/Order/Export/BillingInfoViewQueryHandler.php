<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\Adapter\IntlInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Order\Export\BillingInfoView;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class BillingInfoViewQueryHandler
{
    /** @var BillingInfoRepositoryInterface */
    private $billingInfoRepository;

    /** @var BillingInfo[] */
    private $billingInfos;

    /** @var bool */
    private $isPreload = false;

    /** @var TranslatorInterface */
    private $translator;

    /** @var IntlInterface */
    private $intlAdapter;

    /**
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     * @param TranslatorInterface            $translator
     * @param IntlInterface                  $intlAdapter
     */
    public function __construct(
        BillingInfoRepositoryInterface $billingInfoRepository,
        TranslatorInterface $translator,
        IntlInterface $intlAdapter
    ) {
        $this->billingInfoRepository = $billingInfoRepository;
        $this->translator = $translator;
        $this->intlAdapter = $intlAdapter;
    }

    /**
     * @param Event $event
     */
    public function preload(Event $event): void
    {
        $billingInfos = $this->billingInfoRepository->findByEvent($event);

        foreach ($billingInfos as $billingInfo) {
            $this->billingInfos[$billingInfo->getSheet()->getId()] = $billingInfo;
        }

        $this->isPreload = true;
    }

    /**
     * @param BillingInfoViewQuery $query
     *
     * @return BillingInfoView
     */
    public function handle(BillingInfoViewQuery $query)
    {
        $billingInfoView = new BillingInfoView();

        if ($this->isPreload) {
            if (isset($this->billingInfos[$query->sheet->getId()])) {
                $this->setBillingInfo($this->billingInfos[$query->sheet->getId()], $billingInfoView, $query->adminLocale);
            }
        } else {
            $billingInfo = $this->billingInfoRepository->getBySheet($query->sheet);

            if (null !== $billingInfo) {
                $this->setBillingInfo($billingInfo, $billingInfoView, $query->adminLocale);
            }
        }

        return $billingInfoView;
    }

    /**
     * @param BillingInfo     $billingInfo
     * @param BillingInfoView $billingInfoView
     * @param string          $adminLocale
     */
    private function setBillingInfo(BillingInfo $billingInfo, BillingInfoView $billingInfoView, &$adminLocale): void
    {
        if (null !== $billingInfo->getGender()) {
            $billingInfoView->gender = $this->translator->trans(
                sprintf('gender.%s', $billingInfo->getGender()),
                [],
                'export',
                $adminLocale
            );
        }

        $billingInfoView->firstName = $billingInfo->getFirstname();
        $billingInfoView->lastName = $billingInfo->getLastname();
        $billingInfoView->position = $billingInfo->getFunction();
        $billingInfoView->phone = $billingInfo->getPhone();
        $billingInfoView->mobile = $billingInfo->getMobile();
        $billingInfoView->email = $billingInfo->getEmail();
        $billingInfoView->company = $billingInfo->getCompany();
        $billingInfoView->street = $billingInfo->getAddress()->getStreet();
        $billingInfoView->zipCode = $billingInfo->getAddress()->getZipcode();
        $billingInfoView->city = $billingInfo->getAddress()->getCity();
        $billingInfoView->country = $this->intlAdapter->getCountryName(
            $billingInfo->getAddress()->getCountry(),
            $adminLocale
        );
        $billingInfoView->countryCode = $billingInfo->getAddress()->getCountry();
        $billingInfoView->vatNumber = $billingInfo->getVatNumber();
        $billingInfoView->reference = $billingInfo->getReference();
    }
}
