<?php

namespace Proximum\Vimeet\Tests\Application\Command\StaticFormulation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\StaticFormulation\Create;
use Proximum\Vimeet\Application\Command\StaticFormulation\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;

class CreateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $key = Constant::STATIC_FORMULATION_KEY_AGENDA;
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);
        $event = $this->prophesize(Event::class);
        $translations = [
            'fr' => 'Agenda',
            'en' => 'Planning',
        ];

        $staticFormulation = new StaticFormulation(
            $event->reveal(),
            $key,
            [
                $type1->reveal(),
                $type2->reveal(),
                $type3->reveal(),
            ]
        );
        $staticFormulation->translate('fr', 'Mon Agenda');
        $staticFormulation->translate('en', 'My Planning');

        $staticFormulationRepository = $this->prophesize(StaticFormulationRepositoryInterface::class);
        $staticFormulationRepository->add($staticFormulation)->shouldBeCalled();

        $create = new Create($event->reveal(), $key, $translations);
        $create->types = [
            $type1->reveal(),
            $type2->reveal(),
            $type3->reveal(),
        ];
        $create->translations = [
            'fr' => [
                'title' => 'Mon Agenda',
            ],
            'en' => [
                'title' => 'My Planning',
            ]
        ];

        $handler = new CreateHandler($staticFormulationRepository->reveal());

        $handler->handle($create);
    }
}
