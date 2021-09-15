<?php

namespace Proximum\Vimeet\Tests\Application\Command\Nomenclature;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Nomenclature\Remove;
use Proximum\Vimeet\Application\Command\Nomenclature\RemoveHandler;
use Proximum\Vimeet\Application\Exception\Nomenclature\CanNotBeRemovedException;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Nomenclature\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class RemoveHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $nomenclatureRepository;

    /** @var ObjectProphecy */
    private $removeAuthorizationChecker;

    /** @var ObjectProphecy */
    private $nomenclature;

    public function setUp()
    {
        $this->nomenclature = $this->prophesize(Nomenclature::class);
        $this->nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $this->removeAuthorizationChecker = $this->prophesize(RemoveAuthorizationChecker::class);
    }

    public function testException()
    {
        $this->expectException(CanNotBeRemovedException::class);
        $this->removeAuthorizationChecker
            ->canBeRemoved($this->nomenclature->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $removeHandler = new RemoveHandler(
            $this->nomenclatureRepository->reveal(),
            $this->removeAuthorizationChecker->reveal()
        );
        $removeHandler->handle(new Remove($this->nomenclature->reveal()));
    }

    public function testHandle()
    {
        $this->removeAuthorizationChecker
            ->canBeRemoved($this->nomenclature->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->nomenclatureRepository->remove($this->nomenclature->reveal())->shouldBeCalled();

        $removeHandler = new RemoveHandler(
            $this->nomenclatureRepository->reveal(),
            $this->removeAuthorizationChecker->reveal()
        );
        $removeHandler->handle(new Remove($this->nomenclature->reveal()));
    }
}
