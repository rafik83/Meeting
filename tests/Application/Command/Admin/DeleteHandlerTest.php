<?php
/*
 * This file is part of the PhpStorm project.
 *
 * Copyright (C) PhpStorm
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Admin;

use Proximum\Vimeet\Application\Command\Admin\Delete;
use Proximum\Vimeet\Application\Command\Admin\DeleteHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;

class DeleteHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $admin = $this->prophesize(Admin::class);
        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);

        $plannerJobRepository->countByAdmin($admin->reveal())
            ->shouldBeCalled()
            ->willReturn(0);

        $adminRepository->remove($admin->reveal())
            ->shouldBeCalled();

        $handler = new DeleteHandler($adminRepository->reveal(), $plannerJobRepository->reveal());
        $handler->handle(new Delete($admin->reveal()));
    }

    /**
     * @expectedException \Proximum\Vimeet\Application\Exception\Admin\AdminLinkedToPlannerJobException
     */
    public function testHandleWithAdminLinkedToPlannerJobException(): void
    {
        $admin = $this->prophesize(Admin::class);
        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);

        $plannerJobRepository->countByAdmin($admin->reveal())
            ->shouldBeCalled()
            ->willReturn(2);

        $adminRepository->remove($admin->reveal())
            ->shouldNotBeCalled();

        $handler = new DeleteHandler($adminRepository->reveal(), $plannerJobRepository->reveal());
        $handler->handle(new Delete($admin->reveal()));
    }
}
