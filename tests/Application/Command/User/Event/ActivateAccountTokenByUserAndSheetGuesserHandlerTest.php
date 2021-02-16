<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\Event\ActivateAccountTokenByUserAndSheetGuesser;
use Proximum\Vimeet\Application\Command\User\Event\ActivateAccountTokenByUserAndSheetGuesserHandler;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ActivateAccountTokenByUserAndSheetGuesserHandlerTest extends TestCase
{
    public function testHandle()
    {
        $accountTokenGenerator = $this->prophesize(ActivateAccountTokenGenerator::class);
        $sheetGuesser = $this->prophesize(SheetGuesser::class);
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $activateAccountToken = $this->prophesize(User\ActivateAccountToken::class);

        $user->getLocale()
            ->shouldBeCalled()
            ->willReturn('fr');

        $event->getAvailableLocale('fr')
            ->shouldBeCalled()
            ->willReturn('fr');

        $activeAccountPassword = new ActivateAccountTokenByUserAndSheetGuesser($user->reveal(), $event->reveal());

        $sheetGuesser->getUserSheet($user->reveal(), $event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($sheet->reveal());

        $accountTokenGenerator->generate($user->reveal(), $sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($activateAccountToken->reveal());

        $handler = new ActivateAccountTokenByUserAndSheetGuesserHandler(
            $accountTokenGenerator->reveal(),
            $sheetGuesser->reveal()
        );
        $handler->handle($activeAccountPassword);
    }
}
