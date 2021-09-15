<?php

namespace Proximum\Vimeet\Tests\Application\Components\Messaging;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Messaging\Batch\CreateMessage;
use Proximum\Vimeet\Application\Command\Messaging\Batch\CreateMessageHandler;
use Proximum\Vimeet\Application\Components\Messaging\MessageFactory;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class MessageFactoryTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $createMessageHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->createMessageHandler = $this->prophesize(CreateMessageHandler::class);
    }

    public function testCreateSheetValidated()
    {
        $this->createMessageHandler->handle(
            new CreateMessage(
                $this->event->reveal(),
                Constant::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATED,
                'mail.sheet.validated.subject',
                'MailBundle:Mail:Sheet/sheetValidated.html.twig',
                true
            )
        )->shouldBeCalled();

        $factory = new MessageFactory($this->createMessageHandler->reveal());
        $factory->create($this->event->reveal(), Events::SHEET_VALIDATED);
    }

    public function testCreateSheetValidationValidate()
    {
        $this->createMessageHandler->handle(
            new CreateMessage(
                $this->event->reveal(),
                Constant::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_VALIDATE,
                'mail.sheet.validation.validate.subject',
                'MailBundle:Mail:Sheet/sheetValidationValidate.html.twig',
                true
            )
        )->shouldBeCalled();

        $factory = new MessageFactory($this->createMessageHandler->reveal());
        $factory->create($this->event->reveal(), Events::SHEET_VALIDATION_VALIDATE);
    }

    public function testCreateSheetValidationDraft()
    {
        $this->createMessageHandler->handle(
            new CreateMessage(
                $this->event->reveal(),
                Constant::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_DRAFT,
                'mail.sheet.validation.draft.subject',
                'MailBundle:Mail:Sheet/sheetValidationDraft.html.twig',
                true
            )
        )->shouldBeCalled();

        $factory = new MessageFactory($this->createMessageHandler->reveal());
        $factory->create($this->event->reveal(), Events::SHEET_VALIDATION_DRAFT);
    }

    public function testCreateSheetInvoiced()
    {
        $this->createMessageHandler->handle(
            new CreateMessage(
                $this->event->reveal(),
                Constant::TRANSACTIONAL_MAIL_KEY_SHEET_INVOICED,
                'mail.sheet.invoiced.subject',
                'MailBundle:Mail:Invoice/sheetInvoiced.html.twig',
                true,
                true
            )
        )->shouldBeCalled();

        $factory = new MessageFactory($this->createMessageHandler->reveal());
        $factory->create($this->event->reveal(), Events::SHEET_INVOICED);
    }

    public function testCreateException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->createMessageHandler->handle(Argument::any())->shouldNotBeCalled();

        $factory = new MessageFactory($this->createMessageHandler->reveal());
        $factory->create($this->event->reveal(), 'other');
    }
}
