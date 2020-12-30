<?php

namespace Proximum\Vimeet\Tests\Application\Command\Nomenclature;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Nomenclature\Exception\MissingKeysException;
use Proximum\Vimeet\Application\Command\Nomenclature\Import;
use Proximum\Vimeet\Application\Command\Nomenclature\ImportHandler;
use Proximum\Vimeet\Application\Nomenclature\Import\ImporterInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Event\HasSheet;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ImportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $importer               = $this->prophesize(ImporterInterface::class);
        $hasSheet               = $this->prophesize(HasSheet::class);

        $value        = ['a' => ['label' => ['fr' => 'aaaa']], 'b' => ['label' => ['fr' => 'bbbb']], 'c' => ['label' => ['fr' => 'cccc']]];
        $nomenclature = new Nomenclature('foobar');
        $expected     = new Nomenclature('foobar', 1, $value);

        $importer->import($nomenclature, 'nomenclature.csv', Charset::UTF_8)->will(function (array $args) use ($value) {
            $args[0]->update(1, $value);
        });

        $hasSheet->on()->shouldNotBeCalled();

        $nomenclatureRepository->set($expected)->shouldBeCalled();

        $command = new Import($nomenclature, 'nomenclature.csv', Charset::UTF_8);
        $handler = new ImportHandler($nomenclatureRepository->reveal(), $importer->reveal(), $hasSheet->reveal());
        $handler->handle($command);
    }

    public function testHandleWithEvent()
    {
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $importer               = $this->prophesize(ImporterInterface::class);
        $hasSheet               = $this->prophesize(HasSheet::class);

        $value        = ['a' => ['label' => ['fr' => 'aaaa']], 'b' => ['label' => ['fr' => 'bbbb']], 'c' => ['label' => ['fr' => 'cccc']]];
        $event        = EventFactory::createEvent();
        $nomenclature = new Nomenclature('foobar', 1, [], true, $event);
        $expected     = new Nomenclature('foobar', 1, $value, true, $event);

        $importer->import($nomenclature, 'nomenclature.csv', Charset::UTF_8)->will(function (array $args) use ($value) {
            $args[0]->update(1, $value);
        });

        $hasSheet->on($event)->shouldBeCalled()->willReturn(false);

        $nomenclatureRepository->set($expected)->shouldBeCalled();

        $command = new Import($nomenclature, 'nomenclature.csv', Charset::UTF_8);
        $handler = new ImportHandler($nomenclatureRepository->reveal(), $importer->reveal(), $hasSheet->reveal());
        $handler->handle($command);
    }

    public function testHandleWithHavingSheets()
    {
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $importer               = $this->prophesize(ImporterInterface::class);
        $hasSheet               = $this->prophesize(HasSheet::class);

        $value        = ['a' => ['label' => ['fr' => 'aaaa']], 'b' => ['label' => ['fr' => 'bbbb']], 'c' => ['label' => ['fr' => 'cccc']]];
        $event        = EventFactory::createEvent();
        $nomenclature = new Nomenclature('foobar', 1, [], true, $event);
        $expected     = new Nomenclature('foobar', 1, $value, true, $event);

        $importer->import($nomenclature, 'nomenclature.csv', Charset::UTF_8)->will(function (array $args) use ($value) {
            $args[0]->update(1, $value);
        });

        $hasSheet->on($event)->shouldBeCalled()->willReturn(true);

        $nomenclatureRepository->set($expected)->shouldBeCalled();

        $command = new Import($nomenclature, 'nomenclature.csv', Charset::UTF_8);
        $handler = new ImportHandler($nomenclatureRepository->reveal(), $importer->reveal(), $hasSheet->reveal());
        $handler->handle($command);
    }

    public function testHandleWithHavingSheetsThrowMissingKeysException()
    {
        $this->expectException(MissingKeysException::class);

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $importer               = $this->prophesize(ImporterInterface::class);
        $hasSheet               = $this->prophesize(HasSheet::class);

        $value        = ['a' => ['label' => ['fr' => 'aaaa']], 'b' => ['label' => ['fr' => 'bbbb']]];
        $event        = EventFactory::createEvent();
        $nomenclature = new Nomenclature('foobar', 1, ['a' => ['label' => ['fr' => 'aaaa']], 'b' => ['label' => ['fr' => 'bbbb']], 'c' => ['label' => ['fr' => 'cccc']]], true, $event);
        $expected     = new Nomenclature('foobar', 1, $value, true, $event);

        $importer->import($nomenclature, 'nomenclature.csv', Charset::UTF_8)->will(function (array $args) use ($value) {
            $args[0]->update(1, $value);
        });

        $hasSheet->on($event)->shouldBeCalled()->willReturn(true);

        $nomenclatureRepository->set($expected)->shouldNotBeCalled();

        $command = new Import($nomenclature, 'nomenclature.csv', Charset::UTF_8);
        $handler = new ImportHandler($nomenclatureRepository->reveal(), $importer->reveal(), $hasSheet->reveal());
        $handler->handle($command);
    }
}
