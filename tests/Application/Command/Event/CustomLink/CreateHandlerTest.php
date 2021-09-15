<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\CustomLink;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\CustomLink\Create;
use Proximum\Vimeet\Application\Command\Event\CustomLink\CreateHandler;
use Proximum\Vimeet\Application\Command\StaticFormulation as StaticFormulationCommand;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;

class CreateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // fixtures

        $event = $this->prophesize(Event::class);

        $type = $this->prophesize(Type::class);

        $staticFormulation = $this->prophesize(StaticFormulation::class);

        // dependency's prophesizes

        $customLinkRepository = $this->prophesize(CustomLinkRepositoryInterface::class);
        $customLink = new Event\CustomLink(
            $event->reveal(),
            $staticFormulation->reveal(),
            'phone-icon',
            '#333333',
            '#222222',
            '#111111',
            3
        );

        $customLink->setLocalizedUrl('fr','http://google.fr');
        $customLink->setLocalizedUrl('en','http://google.com');

        $customLinkRepository->add(
            $customLink
        )->shouldBeCalled()
        ;

        $staticFormulationCommandCreateHandler = $this->prophesize(StaticFormulationCommand\CreateHandler::class);
        $staticFormulationCommandCreate = new StaticFormulationCommand\Create(
            $event->reveal(),
            Constant::STATIC_FORMULATION_KEY_CUSTOM_LINK,
            [
                'en' => 'External link',
                'fr' => 'Lien externe',
            ]
        );
        $staticFormulationCommandCreate->types = [$type->reveal()];
        $staticFormulationCommandCreateHandler->handle($staticFormulationCommandCreate)->willReturn(
            $staticFormulation->reveal()
        )
        ;

        // run test

        $command = new Create($event->reveal(), ['en', 'fr',]);
        $command->event = $event->reveal();
        $command->types = [$type->reveal(),];
        $command->priority = 3;
        $command->iconName = 'phone-icon';
        $command->buttonColor = '#111111';
        $command->labelColor = '#222222';
        $command->iconColor = '#333333';
        $command->localizedUrls = [
            'en' => ['title' => 'http://google.com'],
            'fr' => ['title' => 'http://google.fr'],
        ];
        $command->translatedLabels = [
            'en' => ['title' => 'External link'],
            'fr' => ['title' => 'Lien externe'],
        ];

        $handler = new CreateHandler($customLinkRepository->reveal(), $staticFormulationCommandCreateHandler->reveal());
        $handler->handle($command);
    }
}
