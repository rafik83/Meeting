<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Exception\Happening\HappeningException;
use Proximum\Vimeet\Application\Query\Happening\ProgramViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgramController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param UserDomain  $userDomain
     *
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request, EventDomain $eventDomain, Sheet $sheet, UserDomain $userDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

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

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_PROGRAM,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        return $this->render('EventBundle:Program:index.html.twig', [
            'event'                  => $event,
            'sheet'                  => $sheet,
            'program'                => $program,
            'isUserAloneParticipant' => $isUserAloneParticipant,
            'tipTranslationViews'    => $tipTranslationViews,
        ]);
    }
}
