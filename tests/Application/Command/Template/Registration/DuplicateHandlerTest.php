<?php

namespace Proximum\Vimeet\Tests\Application\Command\Template\Registration;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Template\Registration\Duplicate;
use Proximum\Vimeet\Application\Command\Template\Registration\DuplicateHandler;
use Proximum\Vimeet\Application\Command\Template\Registration\DuplicateResult;
use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;

class DuplicateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $cloner = $this->prophesize(RegistrationTemplateCloner::class);

        $event = $this->prophesize(Event::class);
        $template = $this->prophesize(RegistrationTemplate::class);
        $template->getEvent()->willReturn($event->reveal());
        $newTemplate = $this->prophesize(RegistrationTemplate::class);

        $cloner->duplicate($template->reveal(), $event->reveal(), 'new title')
            ->shouldBeCalled()
            ->willReturn($newTemplate->reveal())
        ;

        $command = new Duplicate($template->reveal());
        $command->event = $event->reveal();
        $command->title = 'new title';

        $handler = new DuplicateHandler(
            $cloner->reveal()
        );

        $result = $handler->handle($command);

        $expected = new DuplicateResult($newTemplate->reveal());

        $this->assertEquals($expected, $result);
    }
}
