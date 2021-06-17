<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\Poll;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\Add;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\Update;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SaveHappeningPollAction
{
    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    private CommandBusInterface $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Happening $happening,
        UserDomain $userDomain
    ): JsonResponse {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$happening->hasSpeaker($user)
            || $happening->getEvent() !== $event
            || $sheet->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied to this happening');
        }

        $payload = json_decode($request->getContent(), true);
        if ($payload['id'] ?? false) {
            $this->commandBus->handle(new Update(
                $payload['id'],
                $happening->getId(),
                $payload['title'],
                $payload['choices'],
                $payload['multipleChoice'],
                $payload['publish']
            ));
        } else {
            $this->commandBus->handle(new Add(
                $happening,
                $user,
                $payload['title'],
                $payload['choices'],
                $payload['multipleChoice'],
                $payload['publish']
            ));
        }

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }
}
