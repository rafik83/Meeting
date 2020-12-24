<?php

namespace Proximum\Vimeet\Tests\Domain\Nomenclature;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Nomenclature\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature as NomenclatureObject;

class RemoveAuthorizationCheckerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $registrationTemplateRepository;

    /** @var ObjectProphecy */
    private $sheetTemplateRepository;

    /** @var ObjectProphecy */
    private $templateDataFactory;

    public function setUp()
    {
        $this->registrationTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $this->sheetTemplateRepository = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
    }

    public function testPreload()
    {
        // Context
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(12);
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);
        $sheetTemplate = $this->prophesize(SheetTemplate::class);
        $templateData1 = $this->prophesize(TemplateData::class);
        $templateData2 = $this->prophesize(TemplateData::class);

        $nomenclature1 = $this->prophesize(Nomenclature::class);
        $nomenclature2 = $this->prophesize(Nomenclature::class);
        $nomenclature3 = $this->prophesize(Nomenclature::class);
        $nomenclature1->getId()->willReturn(9);
        $nomenclature2->getId()->willReturn(8);
        $nomenclature3->getId()->willReturn(7);

        $object1 = $this->prophesize(NomenclatureObject::class);
        $object2 = $this->prophesize(NomenclatureObject::class);
        $object3 = $this->prophesize(NomenclatureObject::class);
        $object1->getNomenclatureModel()->willReturn($nomenclature1->reveal());
        $object2->getNomenclatureModel()->willReturn($nomenclature2->reveal());
        $object3->getNomenclatureModel()->willReturn($nomenclature3->reveal());

        // Expected
        $this->registrationTemplateRepository
            ->getTemplateForGivenEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$registrationTemplate->reveal()])
        ;
        $this->sheetTemplateRepository
            ->getTemplateForGivenEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetTemplate->reveal()])
        ;

        $this->templateDataFactory
            ->createFromTemplate($registrationTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn($templateData1->reveal())
        ;
        $templateData1
            ->getNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([$object1->reveal(), $object2->reveal()])
        ;

        $this->templateDataFactory
            ->createFromTemplate($sheetTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn($templateData2->reveal())
        ;
        $templateData2
            ->getNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([$object3->reveal()])
        ;

        // Checker
        $removeAuthorizationChecker = new RemoveAuthorizationChecker(
            $this->registrationTemplateRepository->reveal(),
            $this->sheetTemplateRepository->reveal(),
            $this->templateDataFactory->reveal()
        );
        $removeAuthorizationChecker->preloadForEvent($event->reveal());
        $result =$removeAuthorizationChecker->getNomenclatureUsedOnEvent($event->reveal());
        $expected = [
            9 => $nomenclature1->reveal(),
            8 => $nomenclature2->reveal(),
            7 => $nomenclature3->reveal(),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testCanBeRemoved()
    {
        // Context
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(12);
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);
        $templateData1 = $this->prophesize(TemplateData::class);

        $nomenclature1 = $this->prophesize(Nomenclature::class);
        $nomenclature2 = $this->prophesize(Nomenclature::class);
        $nomenclature3 = $this->prophesize(Nomenclature::class);
        $nomenclature4 = $this->prophesize(Nomenclature::class);
        $nomenclature1->getId()->willReturn(9);
        $nomenclature2->getId()->willReturn(8);
        $nomenclature3->getId()->willReturn(7);
        $nomenclature1->getEvent()->willReturn($event->reveal());
        $nomenclature2->getEvent()->willReturn($event->reveal());
        $nomenclature3->getEvent()->willReturn($event->reveal());
        $nomenclature4->getEvent()->willReturn(null);

        $object1 = $this->prophesize(NomenclatureObject::class);
        $object2 = $this->prophesize(NomenclatureObject::class);
        $object1->getNomenclatureModel()->willReturn($nomenclature1->reveal());
        $object2->getNomenclatureModel()->willReturn($nomenclature2->reveal());

        // Expected
        $this->registrationTemplateRepository
            ->getTemplateForGivenEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$registrationTemplate->reveal()])
        ;
        $this->sheetTemplateRepository->getTemplateForGivenEvent($event->reveal())->shouldBeCalled()->willReturn([]);

        $this->templateDataFactory
            ->createFromTemplate($registrationTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn($templateData1->reveal())
        ;
        $templateData1
            ->getNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([$object1->reveal(), $object2->reveal()])
        ;

        // Checker
        $removeAuthorizationChecker = new RemoveAuthorizationChecker(
            $this->registrationTemplateRepository->reveal(),
            $this->sheetTemplateRepository->reveal(),
            $this->templateDataFactory->reveal()
        );

        // Assertions
        $this->assertTrue($removeAuthorizationChecker->canBeRemoved($nomenclature4->reveal()));
        $this->assertTrue($removeAuthorizationChecker->canBeRemoved($nomenclature3->reveal()));
        $this->assertFalse($removeAuthorizationChecker->canBeRemoved($nomenclature2->reveal()));
        $this->assertFalse($removeAuthorizationChecker->canBeRemoved($nomenclature1->reveal()));
    }
}
