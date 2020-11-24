<?php


namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Sheet;


use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Registration\StepManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Package\IsValidatedRequiredPackageMissing;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Transaction\IsValidatedTransactionMissing;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Sheet\SheetRedirectionMiddleware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

class SheetRedirectionMiddlewareTest extends TestCase
{
    private $router,
    $isValidatedTransactionMissing,
    $isValidatedRequiredPackageMissing,
    $participantRepository,
    $stepManager
    ;

    public function setUp(): void
    {
        $this->router = $this->prophesize(Router::class);
        $this->isValidatedTransactionMissing = $this->prophesize(IsValidatedTransactionMissing::class);
        $this->isValidatedRequiredPackageMissing = $this->prophesize(IsValidatedRequiredPackageMissing::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->stepManager = $this->prophesize(StepManager::class);
    }

    public function testParticipantNullIsValidatedRequiredPackageMissingTrue(): void
    {
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $this->participantRepository->getParticipantForUserAndSheet($user->reveal(), $sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->isValidatedRequiredPackageMissing->isSatisfiedBy($sheet->reveal())->shouldBeCalled()->willReturn(true);
        $this->isValidatedTransactionMissing->isSatisfiedBy($sheet->reveal())->willReturn(false);

        $this->router->generate('event_package_redirect_depending_on_context', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('/sheet/1/package');

        $sheetRedirection = new SheetRedirectionMiddleware(
            $this->router->reveal(),
            $this->isValidatedTransactionMissing->reveal(),
            $this->isValidatedRequiredPackageMissing->reveal(),
            $this->participantRepository->reveal(),
            $this->stepManager->reveal()
        );

        $response = $sheetRedirection->getForceRedirection($sheet->reveal(), $user->reveal());
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('/sheet/1/package', $response->getTargetUrl());
    }

    public function testParticipantNullIsValidatedTransactionMissingTrue(): void
    {
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $this->participantRepository->getParticipantForUserAndSheet($user->reveal(), $sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->isValidatedRequiredPackageMissing->isSatisfiedBy($sheet->reveal())->willReturn(false);
        $this->isValidatedTransactionMissing->isSatisfiedBy($sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->router->generate('event_package_redirect_depending_on_context', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('/sheet/1/package');

        $sheetRedirection = new SheetRedirectionMiddleware(
            $this->router->reveal(),
            $this->isValidatedTransactionMissing->reveal(),
            $this->isValidatedRequiredPackageMissing->reveal(),
            $this->participantRepository->reveal(),
            $this->stepManager->reveal()
        );

        $response = $sheetRedirection->getForceRedirection($sheet->reveal(), $user->reveal());
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('/sheet/1/package', $response->getTargetUrl());
    }

    public function testParticipantNull(): void
    {
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);

        $this->participantRepository->getParticipantForUserAndSheet($user->reveal(), $sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->isValidatedRequiredPackageMissing->isSatisfiedBy($sheet->reveal())->willReturn(false);
        $this->isValidatedTransactionMissing->isSatisfiedBy($sheet->reveal())->willReturn(false);

        $sheetRedirection = new SheetRedirectionMiddleware(
            $this->router->reveal(),
            $this->isValidatedTransactionMissing->reveal(),
            $this->isValidatedRequiredPackageMissing->reveal(),
            $this->participantRepository->reveal(),
            $this->stepManager->reveal()
        );

        $response = $sheetRedirection->getForceRedirection($sheet->reveal(), $user->reveal());
        self::assertNull($response);
    }

    public function testParticipantNotNull(): void
    {
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participantMock = $this->prophesize(Participant::class);

        $this->participantRepository->getParticipantForUserAndSheet($user->reveal(), $sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($participantMock->reveal());

        $this->stepManager->getRedirectStep($sheet->reveal(), $participantMock->reveal())
            ->shouldBeCalled()
            ->willReturn(['redirect'=> true, 'route'=>'event_sheet_default', 'parameters'=>['sheet' => 1]]);

        $this->router->generate('event_sheet_default', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('/sheet/1');

        $sheetRedirection = new SheetRedirectionMiddleware(
            $this->router->reveal(),
            $this->isValidatedTransactionMissing->reveal(),
            $this->isValidatedRequiredPackageMissing->reveal(),
            $this->participantRepository->reveal(),
            $this->stepManager->reveal()
        );

        $response = $sheetRedirection->getForceRedirection($sheet->reveal(), $user->reveal());
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('/sheet/1', $response->getTargetUrl());
    }
}
