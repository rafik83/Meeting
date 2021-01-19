<?php

namespace Proximum\Vimeet\Tests\Application\Command\Template\Registration;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Template\Registration\Create;
use Proximum\Vimeet\Application\Command\Template\Registration\CreateHandler;
use Proximum\Vimeet\Application\Command\Template\Registration\CreateResult;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $dateTime = new \DateTime();
        $repository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);

        $event = $this->prophesize(Event::class);
        $event->getLocales()->willReturn(['fr', 'en']);
        $event->getFallback()->willReturn('fr');

        $template = new RegistrationTemplate(
            'New Template',
            [],
            ['fr', 'en'],
            'fr',
            $dateTime,
            $event->reveal()
        );

        $repository->add($template)->shouldBeCalled();

        $command = new Create($event->reveal());
        $command->title = 'New Template';
        $handler = new CreateHandler(
            $repository->reveal(),
            $dateTime
        );

        $result = $handler->handle($command);
        $expected = new CreateResult($template);

        $this->assertInstanceOf(CreateResult::class, $result);
        $this->assertEquals($expected, $result);
    }
}
