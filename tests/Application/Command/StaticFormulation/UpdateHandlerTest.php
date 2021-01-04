<?php


namespace Proximum\Vimeet\tests\Application\Command\StaticFormulation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\StaticFormulation\Update;
use Proximum\Vimeet\Application\Command\StaticFormulation\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $key = Constant::STATIC_FORMULATION_KEY_AGENDA;

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);
        $type4 = $this->prophesize(Type::class);
        $type5 = $this->prophesize(Type::class);

        $typeGroup = [
            $type1->reveal(),
            $type2->reveal(),
            $type3->reveal(),
        ];

        $newTypeGroup = [
            $type3->reveal(),
            $type4->reveal(),
            $type5->reveal(),
        ];

        $event = $this->prophesize(Event::class);

        $staticFormulation = new StaticFormulation(
            $event->reveal(),
            $key,
            $typeGroup
        );
        $staticFormulation->translate('fr', 'Mon Agenda');
        $staticFormulation->translate('en', 'My Planning');

        $staticFormulationRepository = $this->prophesize(StaticFormulationRepositoryInterface::class);
        $staticFormulationRepository->set($staticFormulation)->shouldBeCalled();

        $update = new Update($staticFormulation);
        $update->types = $newTypeGroup;
        $update->translations = [
            'fr' => [
                'title' => 'Mon Agenda',
            ],
            'en' => [
                'title' => 'My Planning',
            ]
        ];

        $handler = new UpdateHandler($staticFormulationRepository->reveal());

        $handler->handle($update);
    }
}
