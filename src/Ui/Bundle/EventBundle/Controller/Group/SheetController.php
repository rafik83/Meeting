<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Application\Command\Group\Sheet\Create;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Group\Sheet\CreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SheetController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet\Group $sheetGroup
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request, EventDomain $eventDomain, Sheet\Group $sheetGroup)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $createSheet = new Create();
        $form = $this->createForm(CreateType::class, $createSheet, [
            'group' => $sheetGroup,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($createSheet);
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
