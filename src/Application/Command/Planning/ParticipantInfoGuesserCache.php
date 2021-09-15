<?php

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantInfoGuesserCache
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $guesser;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param ParticipantInfoGuesser $guesser
     */
    public function __construct(ParticipantInfoGuesser $guesser)
    {
        $this->guesser = $guesser;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    public function guessParticipantLastName(Participant $participant, $locale)
    {
        $key = $participant->getId() . $locale;

        if (!isset($this->cache['lastName'][$key])) {
            $this->cache['lastName'][$key] = $this->guesser->guessParticipantLastName($participant, $locale);
        }

        return $this->cache['lastName'][$key];
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    public function guessParticipantPosition(Participant $participant, $locale)
    {
        $key = $participant->getId() . $locale;

        if (!isset($this->cache['position'][$key])) {
            $this->cache['position'][$key] = $this->guesser->guessParticipantPositionLabel($participant, $locale);
        }

        return $this->cache['position'][$key];
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    public function guessParticipantCompleteName(Participant $participant, $locale)
    {
        $key = $participant->getId() . $locale;

        if (!isset($this->cache['completeName'][$key])) {
            $this->cache['completeName'][$key] = $this->guesser->guessParticipantCompleteName($participant, $locale);
        }

        return $this->cache['completeName'][$key];
    }
}
