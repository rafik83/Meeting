<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening;

use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Query\Happening\CanEvaluateHappening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;

class PreviousHappeningEvaluationCheckerHandler
{
    public HappeningParticipationRepositoryInterface $happeningParticipationRepository;

    private RouterInterface $router;

    private FlashBagInterface $flashBag;

    private CanEvaluateHappening $canEvaluateHappening;

    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        CanEvaluateHappening $canEvaluateHappening
    ){
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->canEvaluateHappening = $canEvaluateHappening;
    }

    public function __invoke(PreviousHappeningEvaluationChecker $previousHappeningEvaluationChecker): ?RedirectResponse
    {
        $event = $previousHappeningEvaluationChecker->event;
        $user = $previousHappeningEvaluationChecker->user;
        $sheet = $previousHappeningEvaluationChecker->sheet;
        $timeRange = $previousHappeningEvaluationChecker->timeRange;

        $previousEvaluableHappeningParticipation = $this->happeningParticipationRepository->getPreviousMandatoryEvaluation($event, $user, $timeRange->getBegin());

        if (!$previousEvaluableHappeningParticipation instanceof HappeningParticipation) {
            return null;
        }

        if (!$this->canEvaluateHappening->isSatisfiableBy($previousEvaluableHappeningParticipation->getHappening(), $user)) {
            return null;
        }

        if (null !== $previousEvaluableHappeningParticipation->getEvaluation()) {
            return null;
        }

        $this->flashBag->add('warning', 'flash.meeting.evaluation.previous_happening_not_evaluate.warning');

        return new RedirectResponse(
            $this->router->generate(
                Route::HAPPENING_EVALUATION,
                [
                    'sheet' => $sheet->getId(),
                    'happening' => $previousEvaluableHappeningParticipation->getHappening(),
                    'redirectTo' => $previousHappeningEvaluationChecker->origin
                ]
            )
        );
    }
}
