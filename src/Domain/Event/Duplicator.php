<?php

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Domain\Event\BillingConfiguration\Duplicator as BillingConfigurationDuplicator;
use Proximum\Vimeet\Domain\Event\Catalog\External\CatalogVisibilityDuplicator as CatalogVisibilityDuplicator;
use Proximum\Vimeet\Domain\Event\Catalog\External\SearchFacet\Duplicator as ExternalSearchFacetDuplicator;
use Proximum\Vimeet\Domain\Event\Catalog\Internal\SearchFacet\Duplicator as InternalSearchFacetDuplicator;
use Proximum\Vimeet\Domain\Event\Category\Duplicator as CategoryDuplicator;
use Proximum\Vimeet\Domain\Event\Content\Duplicator as ContentDuplicator;
use Proximum\Vimeet\Domain\Event\Message\Duplicator as MessageDuplicator;
use Proximum\Vimeet\Domain\Event\Nomenclature\Duplicator as NomenclatureDuplicator;
use Proximum\Vimeet\Domain\Event\Package\Duplicator as PackageDuplicator;
use Proximum\Vimeet\Domain\Event\PracticalInfo\Duplicator as PracticalInfoDuplicator;
use Proximum\Vimeet\Domain\Event\Product\Duplicator as ProductDuplicator;
use Proximum\Vimeet\Domain\Event\RegistrationTemplate\Duplicator as RegistrationTemplateDuplicator;
use Proximum\Vimeet\Domain\Event\Rule\Duplicator as RuleDuplicator;
use Proximum\Vimeet\Domain\Event\SheetTemplate\Duplicator as SheetTemplateDuplicator;
use Proximum\Vimeet\Domain\Event\StaticFormulation\Duplicator as StaticFormulationDuplicator;
use Proximum\Vimeet\Domain\Event\Tip\Duplicator as TipDuplicator;
use Proximum\Vimeet\Domain\Event\Type\Duplicator as TypeDuplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Content;

class Duplicator
{
    /** @var ContentDuplicator */
    private $contentDuplicator;

    /** @var BillingConfigurationDuplicator */
    private $billingConfigurationDuplicator;

    /** @var PracticalInfoDuplicator */
    private $practicalInfoDuplicator;

    /** @var ProductDuplicator */
    private $productDuplicator;

    /** @var TypeDuplicator */
    private $typeDuplicator;

    /** @var PackageDuplicator */
    private $packageDuplicator;

    /** @var NomenclatureDuplicator */
    private $nomenclatureDuplicator;

    /** @var RegistrationTemplateDuplicator */
    private $registrationTemplateDuplicator;

    /** @var SheetTemplateDuplicator */
    private $sheetTemplateDuplicator;

    /** @var CategoryDuplicator */
    private $categoryDuplicator;

    /** @var RuleDuplicator */
    private $ruleDuplicator;

    /** @var TipDuplicator */
    private $tipDuplicator;

    /** @var InternalSearchFacetDuplicator */
    private $internalSearchFacetDuplicator;

    /** @var ExternalSearchFacetDuplicator */
    private $externalSearchFacetDuplicator;

    /** @var CatalogVisibilityDuplicator */
    private $catalogVisibilityDuplicator;

    /** @var MessageDuplicator */
    private $messageDuplicator;

    /** @var StaticFormulationDuplicator */
    private $staticFormulationDuplicator;

    /**
     * @param ContentDuplicator              $contentDuplicator
     * @param BillingConfigurationDuplicator $billingConfigurationDuplicator
     * @param PracticalInfoDuplicator        $practicalInfoDuplicator
     * @param ProductDuplicator              $productDuplicator
     * @param TypeDuplicator                 $typeDuplicator
     * @param PackageDuplicator              $packageDuplicator
     * @param NomenclatureDuplicator         $nomenclatureDuplicator
     * @param RegistrationTemplateDuplicator $registrationTemplateDuplicator
     * @param SheetTemplateDuplicator        $sheetTemplateDuplicator
     * @param CategoryDuplicator             $categoryDuplicator
     * @param RuleDuplicator                 $ruleDuplicator
     * @param TipDuplicator                  $tipDuplicator
     * @param InternalSearchFacetDuplicator  $internalSearchFacetDuplicator
     * @param ExternalSearchFacetDuplicator  $externalSearchFacetDuplicator
     * @param CatalogVisibilityDuplicator    $catalogVisibilityDuplicator
     * @param MessageDuplicator              $messageDuplicator
     * @param StaticFormulationDuplicator    $staticFormulationDuplicator
     */
    public function __construct(
        ContentDuplicator $contentDuplicator,
        BillingConfigurationDuplicator $billingConfigurationDuplicator,
        PracticalInfoDuplicator $practicalInfoDuplicator,
        ProductDuplicator $productDuplicator,
        TypeDuplicator $typeDuplicator,
        PackageDuplicator $packageDuplicator,
        NomenclatureDuplicator $nomenclatureDuplicator,
        RegistrationTemplateDuplicator $registrationTemplateDuplicator,
        SheetTemplateDuplicator $sheetTemplateDuplicator,
        CategoryDuplicator $categoryDuplicator,
        RuleDuplicator $ruleDuplicator,
        TipDuplicator $tipDuplicator,
        InternalSearchFacetDuplicator $internalSearchFacetDuplicator,
        ExternalSearchFacetDuplicator $externalSearchFacetDuplicator,
        CatalogVisibilityDuplicator $catalogVisibilityDuplicator,
        MessageDuplicator $messageDuplicator,
        StaticFormulationDuplicator $staticFormulationDuplicator
    ) {
        $this->contentDuplicator              = $contentDuplicator;
        $this->billingConfigurationDuplicator = $billingConfigurationDuplicator;
        $this->practicalInfoDuplicator        = $practicalInfoDuplicator;
        $this->productDuplicator              = $productDuplicator;
        $this->typeDuplicator                 = $typeDuplicator;
        $this->packageDuplicator              = $packageDuplicator;
        $this->nomenclatureDuplicator         = $nomenclatureDuplicator;
        $this->registrationTemplateDuplicator = $registrationTemplateDuplicator;
        $this->sheetTemplateDuplicator        = $sheetTemplateDuplicator;
        $this->categoryDuplicator             = $categoryDuplicator;
        $this->ruleDuplicator                 = $ruleDuplicator;
        $this->tipDuplicator                  = $tipDuplicator;
        $this->internalSearchFacetDuplicator  = $internalSearchFacetDuplicator;
        $this->externalSearchFacetDuplicator  = $externalSearchFacetDuplicator;
        $this->catalogVisibilityDuplicator    = $catalogVisibilityDuplicator;
        $this->messageDuplicator              = $messageDuplicator;
        $this->staticFormulationDuplicator = $staticFormulationDuplicator;
    }

    /**
     * @param Event $event
     */
    public function duplicate(Event $event)
    {
        $duplicatorDataStorage = new DuplicatorDataStorage();

        $this->contentDuplicator->duplicate($event, Content::TYPE_TERMS_OF_SALE);
        $this->billingConfigurationDuplicator->duplicate($event);
        $this->practicalInfoDuplicator->duplicate($event);
        $this->productDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->nomenclatureDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->registrationTemplateDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->sheetTemplateDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->packageDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->typeDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->categoryDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->ruleDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->tipDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->internalSearchFacetDuplicator->duplicate($event);
        $this->externalSearchFacetDuplicator->duplicate($event);
        $this->catalogVisibilityDuplicator->duplicate($event, $duplicatorDataStorage);
        $this->messageDuplicator->duplicate($event);
        $this->staticFormulationDuplicator->duplicate($event, $duplicatorDataStorage);
    }
}
