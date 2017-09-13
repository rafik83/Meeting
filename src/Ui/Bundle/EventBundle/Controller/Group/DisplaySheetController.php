<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class DisplaySheetController extends Controller
{
    /**
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param Group         $group
     * @param Sheet         $sheet
     * @param Sheet         $sheetToDisplay
     * @param UserInterface $user
     *
     * @return Response
     */
    public function displayAction(
        Request $request,
        EventDomain $eventDomain,
        Group $group,
        Sheet $sheet,
        Sheet $sheetToDisplay,
        UserInterface $user
    ): Response {

        if ($sheet->getGroup()->getId() !== $group->getId()) {
            throw $this->createNotFoundException('You do not have the right to see this sheet');
        }

        $rules = $this
            ->get('repository.rule_repository')
            ->getBySeerTypeAndSeeableType($sheet->getType(), $sheetToDisplay->getType());

        if (empty($rules)) {
            throw $this->createNotFoundException('You do not have the right to see this sheet');
        }

        $locale = $request->getLocale();

        $templateData = $this->get('template.tagged_data_factory')
            ->buildTaggedDataView($sheetToDisplay, $locale, $rules);

        list ($nomenclatures, $participants, $taggedData) = $this
            ->get('template.sheet.sheet_info_getter')
            ->sheetInfos(
                $eventDomain->getEvent(),
                $sheet,
                $sheetToDisplay,
                $user,
                $locale
            );

        return $this->render('EventBundle:Sheet/Group/Sheet:display.html.twig', [
            'sheet'                           => $sheet,
            'sheetToDisplay'                  => $sheetToDisplay,
            'event'                           => $eventDomain->getEvent(),
            'isMeetingPublished'              => false,
            'isMeetingRequestUpdateLocked'    => true,
            'isMeetingRequestClosed'          => true,
            'isAnsweringMeetingRequestClosed' => true,
            'isRequestMeetingEnabled'         => $sheet !== $sheetToDisplay,
            'isCatalog'                       => true,
            'locale'                          => $locale,
            'templateData'                    => $templateData,
            'taggedData'                      => $taggedData,
            'nomenclatures'                   => $nomenclatures,
            'participants'                    => $participants,
        ]);
    }
}
