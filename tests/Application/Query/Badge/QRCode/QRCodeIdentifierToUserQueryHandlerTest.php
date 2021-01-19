<?php

namespace Proximum\Vimeet\Tests\Application\Query\Badge\QRCode;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierToUserQuery;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierToUserQueryHandler;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class QRCodeIdentifierToUserQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $user = $this->prophesize(User::class);
        $repository = $this->prophesize(UserRepositoryInterface::class);
        $repository->findOneById(320)->shouldBeCalled()->willReturn($user->reveal());

        $handler = new QRCodeIdentifierToUserQueryHandler($repository->reveal());
        $result = $handler->handle(new QRCodeIdentifierToUserQuery('000032000018'));

        $this->assertSame($result, $user->reveal());
    }
}
