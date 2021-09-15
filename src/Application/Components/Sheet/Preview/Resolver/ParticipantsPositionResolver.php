<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Preview\Resolver;

use Proximum\Vimeet\Application\Components\Sheet\Preview\CustomPreviewData;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantsPositionResolver
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /**
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return null|PreviewView
     */
    public function handle(Sheet $sheet, string $locale): ?PreviewView
    {
        $position = $this->getParticipantPosition($sheet, $locale);

        if (null === $position) {
            return null;
        }

        return new PreviewView(
            CustomPreviewData::PARTICIPANTS_POSITION,
            $position,
            CustomPreviewData::PARTICIPANTS_POSITION,
            [],
            $sheet->countParticipants() > 1 // show link if more than one participant
        );
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return null|string
     */
    private function getParticipantPosition(Sheet $sheet, string $locale): ?string
    {
        foreach ($sheet->getParticipantsArray() as $participant) {
            $position = $this->participantInfoGuesser->guessByTag($participant, Tag::PARTICIPANT_POSITION, $locale);

            if (null !== $position) {
                return $position;
            }
        }

        return null;
    }
}
