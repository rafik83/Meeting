<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Exception\Happening\HappeningException;
use Proximum\Vimeet\Application\Query\Happening\ProgramViewQuery;
use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ProgramController extends Controller
{
    /**
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request, EventDomain $eventDomain, Sheet $sheet, UserInterface $user)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        $event = $eventDomain->getEvent();

        try {
            /** @var ProgramView $program */
            $program = $this->get('tactician.commandbus.query')->handle(
                new ProgramViewQuery(
                    $eventDomain->getEvent(),
                    $sheet,
                    $user,
                    $request->getLocale(),
                    null
                )
            );
        } catch (HappeningException $exception) {
            return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
        }

        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($this->getUser(), $sheet);

        return $this->render('EventBundle:Program:index.html.twig', [
            'event'                  => $event,
            'sheet'                  => $sheet,
            'program'                => $program,
            'isUserAloneParticipant' => $isUserAloneParticipant,
        ]);
    }
}
