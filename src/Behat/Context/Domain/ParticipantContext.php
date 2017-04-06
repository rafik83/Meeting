<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\ParticipantContextProxyInterface;

class ParticipantContext implements Context
{
    /** @var ParticipantContextProxyInterface */
    private $participantContextProxy;

    /**
     * @param ParticipantContextProxyInterface $participantContextProxy
     */
    public function __construct(ParticipantContextProxyInterface $participantContextProxy)
    {
        $this->participantContextProxy = $participantContextProxy;
    }

    /**
     * @Given /^there is a participant for this sheet$/
     */
    public function thereIsAParticipantForThisSheet()
    {
        $sheet = $this->participantContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $participant = $this->participantContextProxy->getParticipantManager()->create($sheet->getEvent(), $sheet);
        $this->participantContextProxy->getStorage()->set('participant', $participant);
    }
}
