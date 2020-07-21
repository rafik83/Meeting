<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedSheetListViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\SheetListView;
use Proximum\Vimeet\Application\View\Sheet\SheetParticipantView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Trace\TraceableName;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;

class PaginatedSheetListViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);
        $admin->getDisplayName()->willReturn('admin name');
        $admin->hasAllowedTypes()->willReturn(false);
        $linkedSheets = $this->prophesize(LinkedSheets::class);

        $datetime1 = new \DateTime();
        $datetime2 = new \DateTime();

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $ownerSheet1 = $this->prophesize(User::class);
        $ownerSheet2 = $this->prophesize(User::class);
        $account1 = $this->prophesize(User\Account::class);
        $ownerSheet2->getAccount()->willReturn($account1->reveal());
        $account1->getFirstName()->willReturn('truc');
        $account1->getLastName()->willReturn('muche');
        $ownerSheet1->getEmail()->willReturn('email1@sheet.fr');
        $ownerSheet2->getEmail()->willReturn('email2@sheet.fr');

        $group = $this->prophesize(Sheet\Group::class);

        $sheet1->getOwner()->willReturn($ownerSheet1->reveal());
        $sheet2->getOwner()->willReturn($ownerSheet2->reveal());

        $sheet1->getCreatedAt()->willReturn($datetime1);
        $sheet1->getLastLoginAt()->willReturn($datetime1);

        $sheet2->getCreatedAt()->willReturn($datetime2);
        $sheet2->getLastLoginAt()->willReturn($datetime2);

        $sheet1->getParticipantOwner()->willReturn($participant->reveal());
        $sheet2->getParticipantOwner()->willReturn(null);

        $sheet1->getId()->willReturn(1);
        $sheet1->getState()->willReturn('state1');
        $sheet1->getValidationState()->willReturn('validationState1');
        $sheet1->getCompleteness()->willReturn(45);
        $sheet1->isEnabled()->willReturn(true);
        $sheet1->isInInternalCatalog()->willReturn(true);
        $sheet1->isAccepted()->willReturn(true);
        $sheet1->isValidated()->willReturn(false);
        $sheet1->attend()->willReturn(true);
        $sheet1->getLinkedSheets()->willReturn($linkedSheets->reveal());
        $linkedSheets->getSheets()->willReturn([$sheet2->reveal()]);

        $sheet1->getFollower()->willReturn($admin->reveal());
        $sheet2->getFollower()->willReturn(null);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type1->getTitle('fr')->willReturn('type1');
        $type1->getCategoriesTitles('fr')->willReturn(['Category']);
        $type2->getCategoriesTitles('fr')->willReturn([]);
        $type2->getTitle('fr')->willReturn('type2');
        $sheet1->getType()->willReturn($type1->reveal());
        $sheet1->countParticipant()->willReturn(1);
        $sheet1->getGroup()->willReturn($group->reveal());
        $sheet1->getTraceableName()->willReturn('Sheet');
        $group->getTitle()->willReturn('group title 1');
        $spot = $this->prophesize(Spot::class);
        $sheet1->getSpot()->willReturn($spot->reveal());
        $spot->getReference()->willReturn('Spot 1');

        $sheet2->getId()->willReturn(2);
        $sheet2->attend()->willReturn(false);
        $sheet2->getState()->willReturn('state2');
        $sheet2->getValidationState()->willReturn('validationState2');
        $sheet2->getCompleteness()->willReturn(75);
        $sheet2->isEnabled()->willReturn(false);
        $sheet2->isInCatalog()->willReturn(false);
        $sheet2->getType()->willReturn($type2->reveal());
        $sheet2->countParticipants()->willReturn(2);
        $sheet2->getGroup()->willReturn(null);
        $sheet2->getSpot()->willReturn(null);
        $sheet2->isAccepted()->willReturn(false);
        $sheet2->isValidated()->willReturn(false);
        $sheet1->getCommercialStatus()->willReturn(CommercialStatus::STATUS_INTEREST);
        $sheet2->getCommercialStatus()->willReturn(CommercialStatus::STATUS_DO_NOT_CALL);
        $sheet1->getReminderDate()->willReturn($datetime1);
        $sheet2->getReminderDate()->willReturn($datetime1);
        $sheet2->getLinkedSheets()->willReturn(null);
        $sheet2->getTitle()->willReturn('Title');

        $paginatedResult = new PaginatedResult([$sheet1->reveal(), $sheet2->reveal()], 1, 20, 2);
        $sheetSearchAdapter = $this->prophesize(SheetSearchAdapterInterface::class);
        $sheetSearchAdapter->paginate($event->reveal(), [], null, 1, 20, 'fr', false, [], [], [], null)->shouldBeCalled()->willReturn($paginatedResult);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser->guessSheetTitle($sheet1->reveal(), 'fr')->shouldBeCalled()->willReturn('sheet title 1');
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal(), 'fr')->shouldBeCalled()->willReturn('sheet title 2');
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantFirstName($participant->reveal(), 'fr')->shouldBeCalled()->willReturn('participant first name');
        $participantInfoGuesser->guessParticipantLastName($participant->reveal(), 'fr')->shouldBeCalled()->willReturn('participant last name');
        $impersonate = $this->prophesize(Impersonate::class);
        $impersonate->getEncodedToken($admin->reveal(), $ownerSheet1->reveal())->shouldBeCalled()->willReturn('token1');
        $impersonate->getEncodedToken($admin->reveal(), $ownerSheet2->reveal())->shouldBeCalled()->willReturn('token2');
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->findFullSheets([$sheet1->reveal(), $sheet2->reveal()])->shouldBeCalled()->willReturn([$sheet1->reveal(), $sheet2->reveal()]);
        $traceRepository = $this->prophesize(TraceRepositoryInterface::class);
        $trace = $this->prophesize(Trace::class);
        $trace->getAction()->willReturn('validate');
        $trace->getDate()->willReturn($datetime1);
        $trace->getAuthor()->willReturn('Toto');
        $trace->getObjectType()->willReturn('Sheet');
        $trace->getObjectId()->willReturn(1);

        $traceRepository->getLastByTraceableObjectsAndAction([$sheet1->reveal(), $sheet2->reveal()], TraceableName::SHEET_TRACEABLE_NAME, Trace::ACCEPT)->shouldBeCalled()->willReturn([$trace]);
        $traceRepository->getLastByTraceableObjectsAndAction([$sheet1->reveal(), $sheet2->reveal()], TraceableName::SHEET_TRACEABLE_NAME, Trace::VALIDATE)->shouldBeCalled()->willReturn([]);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $handler = new PaginatedSheetListViewQueryHandler(
            $sheetSearchAdapter->reveal(),
            $sheetInfoGuesser->reveal(),
            $participantInfoGuesser->reveal(),
            $impersonate->reveal(),
            $sheetRepository->reveal(),
            $traceRepository->reveal(),
            $typeRepository->reveal()
        );

        $result = $handler->handle(new PaginatedSheetListViewQuery(
            $event->reveal(),
            [],
            1,
            20,
            'fr',
            $admin->reveal()
        ));

        $expectedSheet1 = new SheetListView(
            1,
            'sheet title 1',
            'state1',
            'validationState1',
            45,
            true,
            true,
            true,
            ['Category'],
            ['Title'],
            'type1',
            new SheetParticipantView('participant first name', 'participant last name', 'email1@sheet.fr'),
            'admin name',
            CommercialStatus::STATUS_INTEREST,
            $datetime1,
            $datetime1,
            $datetime1,
            'token1',
            1,
            true,
            'group title 1',
            'Spot 1',
            $trace->reveal()
        );
        $expectedSheet2 = new SheetListView(
            2,
            'sheet title 2',
            'state2',
            'validationState2',
            75,
            false,
            false,
            false,
            [],
            [],
            'type2',
            new SheetParticipantView('truc', 'muche', 'email2@sheet.fr'),
            '',
            CommercialStatus::STATUS_DO_NOT_CALL,
            $datetime1,
            $datetime2,
            $datetime2,
            'token2',
            2,
            false,
            null,
            null,
            null
        );
        $expected = new PaginatedResult([
            $expectedSheet1,
            $expectedSheet2,
        ], 1, 20, 2);

        self::assertEquals($expected, $result);
    }
}
