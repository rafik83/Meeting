<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Group\Sheet\Create;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Group\Sheet\CreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SheetController extends AbstractController
{
    private CommandBusInterface $commandBus;

    public function __construct(
        CommandBusInterface $commandBus
    ) {
        $this->commandBus = $commandBus;
    }

    public function createAction(Request $request, EventDomain $eventDomain, Sheet\Group $sheetGroup): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $createSheet = new Create();
        $form = $this->createForm(CreateType::class, $createSheet, [
            'group' => $sheetGroup,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($createSheet);
            $this->addFlash('success', 'flash.group.sheet.create.success');

            return $this->redirectToRoute('event_sheet_group_index', [
                'sheetGroup' => $sheetGroup->getId(),
            ]);
        }

        return $this->render('EventBundle:Sheet/Group/Sheet:create.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
        ]);
    }
}
