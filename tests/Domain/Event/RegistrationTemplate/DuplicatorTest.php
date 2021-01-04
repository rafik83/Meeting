<?php

namespace Proximum\Vimeet\Tests\Domain\Event\RegistrationTemplate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\RegistrationTemplate\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $date                       = new \DateTime();
        $eventDuplicated            = EventFactory::createEvent('event duplicated');
        $event                      = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );

        $registrationTemplate = new RegistrationTemplate(
            'registration template',
            [],
            ['fr'],
            'fr',
            $date
        );
        $reflection = new \ReflectionClass(RegistrationTemplate::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($registrationTemplate, 4);

        $clonedRegistrationTemplate = new RegistrationTemplate(
            'registration template',
            [],
            ['fr'],
            'fr',
            $date
        );

        $templateData = new TemplateData('type', [], 'fr', 'fr');
        $registrationTemplate->setValue($templateData->getConfig());

        $registrationTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $registrationTemplateRepository
            ->getTemplateForGivenEvent($eventDuplicated)
            ->shouldBeCalled()
            ->willReturn([$registrationTemplate]);

        $registrationTemplateCloner = $this->prophesize(RegistrationTemplateCloner::class);
        $registrationTemplateCloner
            ->duplicate($registrationTemplate, $event, $registrationTemplate->getTitle())
            ->shouldBeCalled()
            ->willReturn($clonedRegistrationTemplate);

        $duplicatorDataStorage = (new Duplicator(
            $registrationTemplateRepository->reveal(),
            $registrationTemplateCloner->reveal()
        ))->duplicate($event, new DuplicatorDataStorage());

        $this->assertEquals($clonedRegistrationTemplate, $duplicatorDataStorage->registrationTemplates[4]);
    }
}
