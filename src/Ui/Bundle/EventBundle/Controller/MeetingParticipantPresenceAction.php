<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\ParticipantPresenceCommand;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MeetingParticipantPresenceAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(
        CommandBusInterface $commandBus,
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        IsParticipantVisio $isParticipantVisio
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationChecker = $authorizationChecker;
        $this->isParticipantVisio = $isParticipantVisio;
    }

    public function __invoke(Sheet $sheet, Participant $participant, Meeting $meeting): JsonResponse
    {
        if (!$this->isParticipantVisio->isSatisfiedBy($participant)
            || !$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)) {
            throw new AccessDeniedException();
        }

        $this->commandBus->handle(new ParticipantPresenceCommand($participant, $meeting));

        return new JsonResponse('ok');
    }
}
