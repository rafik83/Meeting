<?php

namespace Proximum\Vimeet\Domain\Model\Meeting;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Sheet;

interface MessageSubjectInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return Sheet
     */
    public function getFromSheet();

    /**
     * @return ArrayCollection
     */
    public function getFromParticipants();

    /**
     * @return Sheet
     */
    public function getToSheet();

    /**
     * @return ArrayCollection
     */
    public function getToParticipants();
}
