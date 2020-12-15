<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet\Template;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Exception\Sheet\Template\TemplateObjectUrlObjectIdNotFoundException;
use Proximum\Vimeet\Application\Exception\Sheet\Template\TemplateObjectUrlUnsupportedTypeException;
use Proximum\Vimeet\Application\Query\Sheet\Template\TemplateObjectUrlQuery;
use Proximum\Vimeet\Application\Query\Sheet\Template\TemplateObjectUrlQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ButtonLink;
use Proximum\Vimeet\Domain\Template\TemplateObject\Media;
use Proximum\Vimeet\Domain\Template\TemplateObject\MediaCollection;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\Url;
use Psr\Log\LoggerInterface;

class TemplateObjectUrlQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $eventUrlGenerator;

    /** @var ObjectProphecy */
    private $logger;

    /** @var TemplateObjectUrlQueryHandler */
    private $handler;

    public function setUp()
    {
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->handler = new TemplateObjectUrlQueryHandler(
            $this->templateDataFactory->reveal(),
            $this->eventUrlGenerator->reveal(),
            $this->logger->reveal()
        );
    }

    // tests for "happy path"

    public function testHandleWithUrl()
    {
        $mock = $this->prophesize(Url::class);
        $mock->getUrl()->shouldBecalled()->willReturn('https://www.example.com/path/to/something');
        $url = $this->handleWithTemplateObject($mock->reveal(), null);
        $this->assertEquals('https://www.example.com/path/to/something', $url);
    }

    public function testHandleWithButtonLink()
    {
        $mock = $this->prophesize(ButtonLink::class);
        $mock->getUrl()->shouldBecalled()->willReturn('https://www.example.com/path/to/something');
        $url = $this->handleWithTemplateObject($mock->reveal(), null);
        $this->assertEquals('https://www.example.com/path/to/something', $url);
    }

    public function testHandleWithMediaCollection()
    {
        $mock = $this->prophesize(MediaCollection::class);
        $mock->isTranslatable()->shouldBeCalled()->willReturn(false);
        $mock->getFallback()->shouldBeCalled()->willReturn('fr');

        $mock->getMedias()->shouldBecalled()->willReturn(
            [new Media($mock->reveal(), 'Test lien', 'https://www.example.com/path/to/something', 'test-type')]
        );
        $url = $this->handleWithTemplateObject($mock->reveal(), 0);
        $this->assertEquals('https://www.example.com/path/to/something', $url);
    }

    public function testHandleWithMultiUploadCollectionObject()
    {
        $mock = $this->prophesize(MultiUploadCollectionObject::class);

        $this->eventUrlGenerator
            ->generateEventAbsoluteUrl(Argument::type(Event::class), 'event_sheet_show_uploaded_file', Argument::withKey('sheetToDisplayId'))
            ->shouldBeCalled()
            ->willReturn('/path/to/file-to-download.pdf');

        $mock->getUploads()->shouldBecalled()->willReturn(
            [null, new MultiUploadObject(null, null, 'file-to-download.pdf')]
        );
        $url = $this->handleWithTemplateObject($mock->reveal(), 1);
        $this->assertEquals('/path/to/file-to-download.pdf', $url);
    }

    private function handleWithTemplateObject(TemplateObject $templateObject, ?int $index): string
    {
        $objectId = 'azerzesq';
        $sheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);

        $presentationData = $this->prophesize(TemplateData::class);
        $presentationData->getObjects()->shouldBecalled()->willReturn([]);
        $registrationData = $this->prophesize(TemplateData::class);
        $registrationData->getObjects()->shouldBecalled()->willReturn([$objectId => $templateObject]);

        $this->templateDataFactory->createFromSheet($sheet->reveal(), 'fr')->shouldBeCalled()->willReturn($presentationData->reveal());
        $this->templateDataFactory->createRegistrationFromSheet($sheet->reveal(), 'fr')->shouldBeCalled()->willReturn($registrationData->reveal());

        return $this->handler->handle(new TemplateObjectUrlQuery(
            $sheet->reveal(),
            $event->reveal(),
            'fr',
            $objectId,
            $index
        ));
    }

    // tests exceptions

    public function testObjectIdNotFound()
    {
        $this->expectException(TemplateObjectUrlObjectIdNotFoundException::class);

        $sheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);

        $presentationData = $this->prophesize(TemplateData::class);
        $presentationData->getObjects()->shouldBecalled()->willReturn([]);
        $registrationData = $this->prophesize(TemplateData::class);
        $registrationData->getObjects()->shouldBecalled()->willReturn(['xxx' => $this->prophesize(Url::class)]);

        $this->templateDataFactory->createFromSheet($sheet->reveal(), 'fr')->shouldBeCalled()->willReturn($presentationData->reveal());
        $this->templateDataFactory->createRegistrationFromSheet($sheet->reveal(), 'fr')->shouldBeCalled()->willReturn($registrationData->reveal());

        $this->handler->handle(new TemplateObjectUrlQuery(
            $sheet->reveal(),
            $event->reveal(),
            'fr',
            'yyy',
            null
        ));
    }

    public function testUnsupportedType()
    {
        $this->expectException(TemplateObjectUrlUnsupportedTypeException::class);

        $objectId = 'azerzesq';
        $sheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);

        $presentationData = $this->prophesize(TemplateData::class);
        $presentationData->getObjects()->shouldBecalled()->willReturn([]);
        $registrationData = $this->prophesize(TemplateData::class);
        $registrationData->getObjects()->shouldBecalled()->willReturn([$objectId => $this->prophesize(Media::class)]);

        $this->templateDataFactory->createFromSheet($sheet->reveal(), 'fr')->shouldBeCalled()->willReturn($presentationData->reveal());
        $this->templateDataFactory->createRegistrationFromSheet($sheet->reveal(), 'fr')->shouldBeCalled()->willReturn($registrationData->reveal());

        $this->handler->handle(new TemplateObjectUrlQuery(
            $sheet->reveal(),
            $event->reveal(),
            'fr',
            $objectId,
            null
        ));
    }

}
