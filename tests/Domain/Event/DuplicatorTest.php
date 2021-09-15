<?php

namespace Proximum\Vimeet\Tests\Domain\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\BillingConfiguration\Duplicator as BillingConfigurationDuplicator;
use Proximum\Vimeet\Domain\Event\Catalog\External\CatalogVisibilityDuplicator as CatalogVisibilityDuplicator;
use Proximum\Vimeet\Domain\Event\Catalog\External\SearchFacet\Duplicator as ExternalSearchFacetDuplicator;
use Proximum\Vimeet\Domain\Event\Catalog\Internal\SearchFacet\Duplicator as InternalSearchFacetDuplicator;
use Proximum\Vimeet\Domain\Event\Category\Duplicator as CategoryDuplicator;
use Proximum\Vimeet\Domain\Event\Content\Duplicator as ContentDuplicator;
use Proximum\Vimeet\Domain\Event\Duplicator;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
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
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $event                 = EventFactory::createEvent('Concert de Jean Michel Jarre');
        $duplicatorDataStorage = new DuplicatorDataStorage();

        $contentDuplicator = $this->prophesize(ContentDuplicator::class);
        $contentDuplicator->duplicate($event, Content::TYPE_TERMS_OF_SALE)->shouldBeCalled();

        $billingConfigurationDuplicator = $this->prophesize(BillingConfigurationDuplicator::class);
        $billingConfigurationDuplicator->duplicate($event)->shouldBeCalled();

        $practicalInfoDuplicator = $this->prophesize(PracticalInfoDuplicator::class);
        $practicalInfoDuplicator->duplicate($event)->shouldBeCalled();

        $productDuplicator = $this->prophesize(ProductDuplicator::class);
        $productDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $nomenclatureDuplicator = $this->prophesize(NomenclatureDuplicator::class);
        $nomenclatureDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $registrationTemplateDuplicator = $this->prophesize(RegistrationTemplateDuplicator::class);
        $registrationTemplateDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $sheetTemplateDuplicator = $this->prophesize(SheetTemplateDuplicator::class);
        $sheetTemplateDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $packageDuplicator = $this->prophesize(PackageDuplicator::class);
        $packageDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $typeDuplicator = $this->prophesize(TypeDuplicator::class);
        $typeDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $categoryDuplicator = $this->prophesize(CategoryDuplicator::class);
        $categoryDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $ruleDuplicator = $this->prophesize(RuleDuplicator::class);
        $ruleDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $tipDuplicator = $this->prophesize(TipDuplicator::class);
        $tipDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $internalSearchFacetDuplicator = $this->prophesize(InternalSearchFacetDuplicator::class);
        $internalSearchFacetDuplicator->duplicate($event)->shouldBeCalled();

        $externalSearchFacetDuplicator = $this->prophesize(ExternalSearchFacetDuplicator::class);
        $externalSearchFacetDuplicator->duplicate($event)->shouldBeCalled();

        $catalogVisibilityDuplicator = $this->prophesize(CatalogVisibilityDuplicator::class);
        $catalogVisibilityDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        $messageDuplicator = $this->prophesize(MessageDuplicator::class);
        $messageDuplicator->duplicate($event)->shouldBeCalled();

        $staticFormulationDuplicator = $this->prophesize(StaticFormulationDuplicator::class);
        $staticFormulationDuplicator->duplicate($event, $duplicatorDataStorage)->shouldBeCalled();

        (new Duplicator(
            $contentDuplicator->reveal(),
            $billingConfigurationDuplicator->reveal(),
            $practicalInfoDuplicator->reveal(),
            $productDuplicator->reveal(),
            $typeDuplicator->reveal(),
            $packageDuplicator->reveal(),
            $nomenclatureDuplicator->reveal(),
            $registrationTemplateDuplicator->reveal(),
            $sheetTemplateDuplicator->reveal(),
            $categoryDuplicator->reveal(),
            $ruleDuplicator->reveal(),
            $tipDuplicator->reveal(),
            $internalSearchFacetDuplicator->reveal(),
            $externalSearchFacetDuplicator->reveal(),
            $catalogVisibilityDuplicator->reveal(),
            $messageDuplicator->reveal(),
            $staticFormulationDuplicator->reveal()
        ))->duplicate($event);
    }
}
