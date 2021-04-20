<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Sheet\GetSheetIdsByFiltersQuery;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;
use Proximum\Vimeet\Domain\ConditionRules\Storage\RuleStorageInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter\SheetFilterSubmittedDataGetter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet\ExportUploadedObjectsBySheetsAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExportUploadedObjectsBySheetsActionTest extends TestCase
{
    public function test__invoke(): void
    {
        $request = $this->prophesize(Request::class);
        $request->getLocale()->shouldBeCalled()->willReturn('fr');
        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $sheetFilterSubmittedDataGetter = $this->prophesize(SheetFilterSubmittedDataGetter::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $flashBag = $this->prophesize(FlashBagInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $queryBus = $this->prophesize(QueryBusInterface::class);
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(1);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');
        $admin = $this->prophesize(Admin::class);
        $adminDomain = $this->prophesize(AdminDomain::class);
        $adminDomain->getAdmin()->willReturn($admin);

        $urlGenerator->generate('admin_sheet', ['event' => 1])
            ->shouldBeCalled()
            ->willReturn('/sheets/1');

        $authorizationChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $authorizationChecker
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true);

        $sheetFilterSubmittedDataGetter->handle($event->reveal(), $admin->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(['enabled' => true]);

        $queryBus->handle(new GetSheetIdsByFiltersQuery($event->reveal(), ['enabled' => true], 'fr'))
            ->shouldBeCalled()
            ->willReturn(new SheetIdsView([1, 2, 3]));

        $extraData = new Event\ExtraData(
            $event->reveal(),
            Type::ADMIN_SHEET_BATCH_IDS,
            '1, 2, 3',
            $dateTime
        );

        $extraDataRepository->add($extraData);

        $ruleStorage = $this->prophesize(RuleStorageInterface::class);
        $ruleStorage->getRules($event->reveal(), 'fr', 'sheet')
            ->shouldBeCalled()
            ->willReturn(null);

        $jobQueue->exportUploadedObjectsBySheets($event->reveal(), $admin->reveal(), $extraData)->shouldBeCalled();
        $flashBag->add('success', 'flash.admin.event.export.uploaded_objects.success')->shouldBeCalled();

        $action = new ExportUploadedObjectsBySheetsAction(
            $authorizationChecker->reveal(),
            $sheetFilterSubmittedDataGetter->reveal(),
            $extraDataRepository->reveal(),
            $urlGenerator->reveal(),
            $flashBag->reveal(),
            $jobQueue->reveal(),
            $queryBus->reveal(),
            $dateTime,
            $ruleStorage->reveal()
        );
        $action($request->reveal(), $event->reveal(), $adminDomain->reveal());
    }
}
