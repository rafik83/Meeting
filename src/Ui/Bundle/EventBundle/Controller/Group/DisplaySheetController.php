<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;
use Proximum\Vimeet\Domain\Sheet\SheetInfoGetter;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class DisplaySheetController extends AbstractController
{
    private SheetRepositoryInterface $sheetRepository;
    private CanSeeSheet $canSeeSheet;
    private SheetInfoGetter $sheetInfoGetter;
    private MeetingPublishedAccessChecker $meetingPublishedAccessChecker;
    private TaggedDataFactory $taggedDataFactory;
    private MeetingRequestAccessChecker $meetingRequestAccessChecker;
    private AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker;
    private Applyer $ruleApplyer;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        CanSeeSheet $canSeeSheet,
        SheetInfoGetter $sheetInfoGetter,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        TaggedDataFactory $taggedDataFactory,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker,
        Applyer $ruleApplyer
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->canSeeSheet = $canSeeSheet;
        $this->sheetInfoGetter = $sheetInfoGetter;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->taggedDataFactory = $taggedDataFactory;
        $this->meetingRequestAccessChecker = $meetingRequestAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
        $this->ruleApplyer = $ruleApplyer;
    }

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

    private function displaySheet(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        int $sheetToDisplayId,
        UserInterface $user = null
    ): Response {
        $event = $eventDomain->getEvent();

        $sheetToDisplay = $this->sheetRepository
            ->getSheetById($sheetToDisplayId);

        if (null === $sheetToDisplay
            || $eventDomain->getEvent() !== $sheetToDisplay->getEvent()
            || !$sheetToDisplay->isInInternalCatalog()
        ) {
            throw $this->createAccessDeniedException('Sheet not found');
        }

        if (false === $this->canSeeSheet->isSatisfiedBy($sheet, $sheetToDisplay)) {
            throw $this->createNotFoundException('You do not have the right to see this sheet');
        }

        $locale = $request->getLocale();

        try {
            list($nomenclatures, $participants, $taggedData) = $this
                ->sheetInfoGetter
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

        $rules = $this->ruleRepository
            ->getBySeerSheetAndSeeableSheet($sheet, $sheetToDisplay);

        $templateData = $this->taggedDataFactory
            ->buildTaggedDataView($sheetToDisplay, $locale, $rules);

        $isMeetingPublished = $this->meetingPublishedAccessChecker
            ->allowedToAccess($event);

        $isMeetingRequestUpdateLocked = $event->getConfiguration()->isMeetingRequestUpdateLocked();
        $isMeetingRequestClosed = !$this->meetingRequestAccessChecker
            ->allowedToAccess($event)
        ;
        $isAnsweringMeetingRequestClosed = !$this->answeringMeetingRequestAccessChecker
            ->allowedToAccess($event)
        ;

        $this->ruleApplyer->applyRuleForTemplate($templateData, $rules);
        $this->ruleApplyer->applyRuleForCardList($participants, $rules);

        return $this->render('EventBundle:Sheet/Group/Sheet:display.html.twig', [
            'sheet' => $sheet,
            'sheetToDisplay' => $sheetToDisplay,
            'event' => $eventDomain->getEvent(),
            'isMeetingPublished' => $isMeetingPublished,
            'isMeetingRequestUpdateLocked' => $isMeetingRequestUpdateLocked,
            'isMeetingRequestClosed' => $isMeetingRequestClosed,
            'isAnsweringMeetingRequestClosed' => $isAnsweringMeetingRequestClosed,
            'isRequestMeetingEnabled' => false,
            'isCatalog' => true,
            'hideMeetingRequest' => true,
            'locale' => $locale,
            'templateData' => $templateData,
            'taggedData' => $taggedData,
            'nomenclatures' => $nomenclatures,
            'participants' => $participants,
        ]);
    }
}
