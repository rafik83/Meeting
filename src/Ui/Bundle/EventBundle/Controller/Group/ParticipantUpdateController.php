<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Group\Participant\UpdateUsersSheets;
use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Group\GroupViewQuery;
use Proximum\Vimeet\Application\View\Group\Participant\UpdateUsersSheetsResultView;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Group\UsersSheetsType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParticipantUpdateController extends AbstractController
{
    private TranslatorInterface $translator;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        TranslatorInterface $translator,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->translator = $translator;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function updateAction(Request $request, EventDomain $eventDomain, Group $sheetGroup): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $event = $eventDomain->getEvent();

        $groupView = $this->queryBus->handle(
            new GroupViewQuery($sheetGroup)
        );

        $userParticipantViews = $this->queryBus->handle(
            new UsersParticipantViewQuery($sheetGroup)
        );

        $updateUsersSheets = new UpdateUsersSheets($sheetGroup, $userParticipantViews);

        $form = $this->createForm(
            UsersSheetsType::class,
            $updateUsersSheets,
            [
                'group' => $sheetGroup,
                'updateUsersSheets' => $updateUsersSheets,
            ]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            /** @var UpdateUsersSheetsResultView[] $updateUsersSheetsResultViews */
            $updateUsersSheetsResultViews = $this->commandBus->handle($updateUsersSheets);

            $updateUsersSheetsResultMessage = [];

            foreach ($updateUsersSheetsResultViews as $updateUsersSheetsResultView) {
                $updateUsersSheetsResultMessage[] = $this->translator->trans('flash.' . $updateUsersSheetsResultView->type, [
                    '%participantFullname%' => $updateUsersSheetsResultView->participantFullname,
                    '%sheetTitle%' => $updateUsersSheetsResultView->sheetTitle,
                ], 'flashes');
            }

            $this->addFlash(
                0 === count($updateUsersSheetsResultMessage) ? 'success' : 'warning',
                $this->translator->transChoice(
                    'flash.group.participant.update_success',
                    count($updateUsersSheetsResultMessage),
                    ['%result%' => implode("\n", $updateUsersSheetsResultMessage)],
                    'flashes'
                )
            );

            return $this->redirectToRoute(
                'event_sheet_group_participant_update',
                ['sheetGroup' => $sheetGroup->getId()]
            );
        }

        return $this->render('EventBundle:Sheet/Group/Participant:update.html.twig', [
            'event'                => $event,
            'groupView'            => $groupView,
            'userParticipantViews' => $userParticipantViews,
            'form'                 => $form->createView(),
        ]);
    }
}
