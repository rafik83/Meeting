<?php

namespace Proximum\Vimeet\Application\Components\Planning\Formatter;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Service\MarkdownFormatter;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class SheetPlanningFormatter
{
    /**
     * @var ParticipantPlanningFormatter
     */
    private $participantPlanningFormatter;

    /**
     * @var UnallocatedFormatter
     */
    private $unallocatedFormatter;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param UnallocatedFormatter         $unallocatedFormatter
     * @param ParticipantInfoGuesser       $participantInfoGuesser
     */
    public function __construct(
        ParticipantPlanningFormatter $participantPlanningFormatter,
        UnallocatedFormatter $unallocatedFormatter,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->unallocatedFormatter         = $unallocatedFormatter;
        $this->participantInfoGuesser       = $participantInfoGuesser;
    }

    /**
     * Format the planning of a sheet with the ability to force the first planning to be display to be given as a parameter
     *
     * @param Sheet            $sheet
     * @param string           $locale
     * @param Participant|null $firstParticipantToDisplay
     *
     * @return string
     */
    public function format(Sheet $sheet, $locale, Participant $firstParticipantToDisplay = null)
    {
        $planning = '';

        if (null !== $firstParticipantToDisplay) {
            $planning .= $this->formatParticipantPlanning($firstParticipantToDisplay, $locale);
        }

        foreach ($sheet->getParticipants()->toArray() as $participant) {
            if ($participant === $firstParticipantToDisplay) {
                continue;
            }

            $planning .= $this->formatParticipantPlanning($participant, $locale);
        }

        return $planning;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    private function formatParticipantPlanning(Participant $participant, $locale)
    {
        // display participant name
        $planning = MarkdownFormatter::newLine(
            MarkdownFormatter::heading(
                $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale),
                2
            )
        );

        $event = $participant->getSheet()->getEvent();
        $user = $participant->getUser();

        // display participant planning
        $planning .= MarkdownFormatter::newLine(
            $this->participantPlanningFormatter->formatPlanningFromUserAndEvent($user, $event, $locale)
        );

        return $planning;
    }

    /**
     * Format the planning of a sheet with the ability to force the first planning to be display to be
     * given as a parameter with the unallocated meetings
     *
     * @param Sheet            $sheet
     * @param string           $locale
     * @param Participant|null $firstParticipantToDisplay
     *
     * @return string
     */
    public function formatWithUnallocated(Sheet $sheet, $locale, Participant $firstParticipantToDisplay = null)
    {
        $planning    = $this->format($sheet, $locale, $firstParticipantToDisplay);
        $unallocated = $this->unallocatedFormatter->format($sheet, $locale);

        if (empty($unallocated)) {
            return $planning;
        }

        // In this case, the planning has already a new line at the end
        return $planning . $unallocated;
    }
}
