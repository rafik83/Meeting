<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Type;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\Type\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    /** @var \DateTimeInterface */
    private $date;

    /** @var Event */
    private $eventDuplicated;

    /** @var Event */
    private $event;

    /** @var Package */
    private $oldPackageTemplate;

    /** @var Package */
    private $packageTemplate;

    /** @var RegistrationTemplate */
    private $registrationTemplate;

    /** @var RegistrationTemplate */
    private $oldRegistrationTemplate;

    /** @var SheetTemplate */
    private $sheetTemplate;

    /** @var SheetTemplate */
    private $oldSheetTemplate;

    public function setUp()
    {
        $this->date            = new \DateTime();
        $this->eventDuplicated = EventFactory::createEvent('event duplicated');
        $this->event           = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $this->eventDuplicated
        );

        $this->packageTemplate    = new Package($this->event, 'package title', $this->date);
        $this->oldPackageTemplate = new Package($this->event, 'package title', $this->date);
        $reflection               = new \ReflectionClass(Package::class);
        $property                 = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->oldPackageTemplate, 3);

        $this->registrationTemplate = new RegistrationTemplate(
            'registration template',
            [],
            ['fr'],
            'fr',
            $this->date
        );
        $this->oldRegistrationTemplate = new RegistrationTemplate(
            'registration template',
            [],
            ['fr'],
            'fr',
            $this->date
        );
        $reflection = new \ReflectionClass(RegistrationTemplate::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->oldRegistrationTemplate, 5);

        $this->sheetTemplate = new SheetTemplate(
            'sheet template title',
            [],
            ['fr'],
            'fr',
            $this->date
        );
        $this->oldSheetTemplate = new SheetTemplate(
            'sheet template title',
            [],
            ['fr'],
            'fr',
            $this->date
        );
        $reflection = new \ReflectionClass(SheetTemplate::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->oldSheetTemplate, 6);
    }

    public function testDuplicate()
    {
        $duplicatorDataStorage = new DuplicatorDataStorage();

        $duplicatorDataStorage->sheetTemplates        = [6 => $this->sheetTemplate];
        $duplicatorDataStorage->registrationTemplates = [5 => $this->registrationTemplate];
        $duplicatorDataStorage->packageTemplates      = [3 => $this->packageTemplate];

        $expectedType = new Type($this->event);
        $expectedType->setSheetTemplate($this->sheetTemplate);
        $expectedType->setRegistrationTemplate($this->registrationTemplate);
        $expectedType->setPackage($this->packageTemplate);

        $type = new Type($this->eventDuplicated);
        $type->setSheetTemplate($this->oldSheetTemplate);
        $type->setRegistrationTemplate($this->oldRegistrationTemplate);
        $type->setPackage($this->oldPackageTemplate);
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($type, 8);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getTypesByEvent($this->eventDuplicated)->shouldBeCalled()->willReturn([$type]);

        $typeRepository->add($expectedType)->shouldBeCalled();

        $expected = (new Duplicator($typeRepository->reveal()))->duplicate($this->event, $duplicatorDataStorage);
        $this->assertEquals($expected->types[8], $expectedType);
    }
}
