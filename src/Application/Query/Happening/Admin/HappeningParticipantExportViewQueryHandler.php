<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningParticipantListView;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningParticipantView;
use Proximum\Vimeet\Domain\Happening\HappeningDateHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;

class HappeningParticipantExportViewQueryHandler
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var QuestionRepositoryInterface
     */
    private $questionRepository;

    /**
     * @var GroupNameResolver
     */
    private $groupNameResolver;
    /**
     * @var SheetGuesser
     */
    private $sheetGuesser;

    private HappeningParticipationRepositoryInterface $happeningParticipationRepository;

    /**
     * HappeningParticipantViewQueryHandler constructor.
     *
     * @param HappeningRepositoryInterface $happeningRepository
     * @param QuestionRepositoryInterface  $questionRepository
     * @param GroupNameResolver            $groupNameResolver
     * @param SheetGuesser                 $sheetGuesser
     */
    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        QuestionRepositoryInterface $questionRepository,
        GroupNameResolver $groupNameResolver,
        SheetGuesser $sheetGuesser,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->questionRepository  = $questionRepository;
        $this->groupNameResolver   = $groupNameResolver;
        $this->sheetGuesser        = $sheetGuesser;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    /**
     * @param HappeningParticipantExportViewQuery $query
     *
     * @throws EmptyHappeningParticipationException
     *
     * @return HappeningParticipantListView
     */
    public function handle(HappeningParticipantExportViewQuery $query)
    {
        // preload happenings with participations and questions
        $happenings = $this->happeningRepository->findHappeningParticipant($query->event);

        $happeningParticipantViews = [];

        foreach ($happenings as $happening) {
            $participations = $happening->getParticipations();

            foreach ($participations as $participation) {
                if (!$participation->isDisabled()) {
                    $happeningParticipantViews[] = $this->buildView(
                        $happening,
                        $participation,
                        $query->event,
                        $query->locale
                    );
                }
            }
        }

        if (0 === count($happeningParticipantViews)) {
            throw new EmptyHappeningParticipationException();
        }

        return new HappeningParticipantListView($happeningParticipantViews);
    }

    private function buildView(Happening $happening, HappeningParticipation $participation, Event $event, string $locale): HappeningParticipantView
    {
        $user = $participation->getUser();
        $scannedAt = $this->happeningParticipationRepository->getScannedAt($participation);

        try {
            $sheetName = $this->groupNameResolver->resolve($event, $user);
        } catch (\Exception $exception) {
            $sheetName = '';
        }

        try {
            $sheet = $this->sheetGuesser->getUserSheet($user, $event, $locale);
        } catch (\Exception $exception) {
            $sheet = null;
        }

        $timezone = $event->getTimeZone();

        $questionRows = null !== $sheet ? $this->questionRepository->findByHappeningAndUser($happening, $user) : [];

        $participantQuestions = new HappeningParticipantQuestions();

        /** @var Happening\Question $question */
        foreach ($questionRows as [$question, $votes]) {
            if (null === $question) {
                break;
            }

            if ($question->getAskedDuringWebinar()) {
                $participantQuestions->addQuestionWebinar(
                    $question->getContent(),
                    $question->getReplyContent(),
                    $votes,
                    HappeningDateHelper::getDateTime($question->getCreatedAt(), $locale, $timezone)
                );
            } else {
                $participantQuestions->setQuestionRegister($question->getContent());
            }
        }

        $happeningParticipantView = new HappeningParticipantView(
            $happening->getId(),
            HappeningDateHelper::getHour($happening->getBegin(), $locale, $timezone),
            HappeningDateHelper::getHour($happening->getEnd(), $locale, $timezone),
            HappeningDateHelper::getDay($happening->getBegin(), $locale, $timezone),
            $happening->getTitle($locale),
            null !== $sheet ? $sheet->getId() : '',
            $user->getId(),
            $participantQuestions->getQuestionRegister(),
            $participantQuestions->getQuestionsWebinar(),
            $participantQuestions->getReplies(),
            $participantQuestions->getVotes(),
            $participantQuestions->getDateTimes(),
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getPosition(),
            $sheetName,
            $user->getPhone(),
            $participation->getEvaluation(),
            $scannedAt ? HappeningDateHelper::getDateTime($scannedAt, $locale, $timezone) : ''
        );

        return $happeningParticipantView;
    }
}
