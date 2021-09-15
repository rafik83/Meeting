<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ParticipantImportAccessVoter extends Voter
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * ParticipantImportAccessVoter constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return 'PERMISSION_PARTICIPANT_IMPORT_ACCESS' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return null !== $this->session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE);
    }
}
