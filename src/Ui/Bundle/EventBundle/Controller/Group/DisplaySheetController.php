<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class DisplaySheetController extends Controller
{
    /**
     * @param Request            $request
     * @param EventDomain        $eventDomain
     * @param Group              $group
     * @param Sheet              $sheet
     * @param int                $sheetToDisplayId
     * @param UserInterface|null $user
     *
     * @return Response
     */
    public function displayForGroupManagerAction(
        Request $request,
        EventDomain $eventDomain,
        Group $group,
        Sheet $sheet,
        int $sheetToDisplayId,
        UserInterface $user = null
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $group);

        if ($sheet->getGroup()->getId() !== $group->getId()) {
            throw $this->createNotFoundException('You do not have the right to see this sheet');
        }

        return $this->displaySheet($request, $eventDomain, $sheet, $sheetToDisplayId, $user);
    }

    /**
     * @param Request            $request
     * @param EventDomain        $eventDomain
     * @param Sheet              $sheet
     * @param int                $sheetToDisplayId
     * @param UserInterface|null $user
     *
     * @return Response
     */
    public function displayForMultiSheetUserAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        int $sheetToDisplayId,
        UserInterface $user = null
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$sheet->hasUser($user)) {
            throw $this->createNotFoundException();
        }

        return $this->displaySheet($request, $eventDomain, $sheet, $sheetToDisplayId, $user);
    }

    /**
     * @param Request            $request
     * @param EventDomain        $eventDomain
     * @param Sheet              $sheet
     * @param int                $sheetToDisplayId
     * @param UserInterface|null $user
     *
     * @return Response
     */
    private function displaySheet(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        int $sheetToDisplayId,
        UserInterface $user = null
    ): Response {
        $event = $eventDomain->getEvent();

        $sheetToDisplay = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetById($sheetToDisplayId);

        if (null === $sheetToDisplay
            || $eventDomain->getEvent() !== $sheetToDisplay->getEvent()
            || !$sheetToDisplay->isInCatalog()
        ) {
            throw $this->createAccessDeniedException('Sheet not found');
        }

        $canSeeSheet = $this->get(CanSeeSheet::class);

        if (false === $canSeeSheet->isSatisfiedBy($sheet, $sheetToDisplay)) {
            throw $this->createNotFoundException('You do not have the right to see this sheet');
        }

        $locale = $request->getLocale();

        try {
            list($nomenclatures, $participants, $taggedData) = $this
                ->get('template.sheet.sheet_info_getter')
                ->sheetInfos(
                    $eventDomain->getEvent(),
                    $sheet,
                    $sheetToDisplay,
                    $user,
                    $locale
                );
        } catch (AccessDeniedException $exception) {
            throw $this->createAccessDeniedException();
        }

        $rules = $this
            ->get('repository.rule_repository')
            ->getBySeerSheetAndSeeableSheet($sheet, $sheetToDisplay);

        $templateData = $this->get('template.tagged_data_factory')
            ->buildTaggedDataView($sheetToDisplay, $locale, $rules);

        $isMeetingPublished = $this
            ->get('domain.key_dates.checker.meeting_published_access_checker')
            ->allowedToAccess($event);

        $isMeetingRequestUpdateLocked = $event->getConfiguration()->isMeetingRequestUpdateLocked();
        $isMeetingRequestClosed          = !$this
            ->get('domain.key_dates.checker.meeting_request_access_checker')
            ->allowedToAccess($event)
        ;
        $isAnsweringMeetingRequestClosed = !$this
            ->get('domain.key_dates.checker.answering_meeting_request_access_checker')
            ->allowedToAccess($event)
        ;

        $ruleApplyer = $this->get('domain.rule.applyer');
        $ruleApplyer->applyRuleForTemplate($templateData, $rules);
        $ruleApplyer->applyRuleForCardList($participants, $rules);

        return $this->render('EventBundle:Sheet/Group/Sheet:display.html.twig', [
            'sheet'                           => $sheet,
            'sheetToDisplay'                  => $sheetToDisplay,
            'event'                           => $eventDomain->getEvent(),
            'isMeetingPublished'              => $isMeetingPublished,
            'isMeetingRequestUpdateLocked'    => $isMeetingRequestUpdateLocked,
            'isMeetingRequestClosed'          => $isMeetingRequestClosed,
            'isAnsweringMeetingRequestClosed' => $isAnsweringMeetingRequestClosed,
            'isRequestMeetingEnabled'         => false,
            'isCatalog'                       => true,
            'hideMeetingRequest'              => true,
            'locale'                          => $locale,
            'templateData'                    => $templateData,
            'taggedData'                      => $taggedData,
            'nomenclatures'                   => $nomenclatures,
            'participants'                    => $participants,
        ]);
    }
}
