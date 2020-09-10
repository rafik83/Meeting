<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\StreamCommand;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Application\View\Happening\Webinar\StreamDTO;
use Proximum\Vimeet\Domain\Happening\Webinar\Stream;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Happening\ParticipationVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use function in_array;

class StreamAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CanAccessToWebinar */
    private $canAccessToWebinar;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CanAccessToWebinar $canAccessToWebinar,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->canAccessToWebinar = $canAccessToWebinar;
        $this->commandBus = $commandBus;
    }

    public function __invoke(
        Request $request,
        Sheet $sheet,
        Happening $happening,
        UserDomain $userDomain
    ) {
        $event = $sheet->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->authorizationCheckerAdapter->isGranted(ParticipationVoter::PARTICIPATE, $sheet)
            || !$this->canAccessToWebinar->isSatisfiableBy($happening, $user)
            || $happening->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied to this happening');
        }

        $streamId = $request->request->get('streamId');
        $streamAction = $request->request->get('action');
        $type = $request->request->get('type');

        if (null === $streamId
            || null === $streamAction
            || null === $type
            || !in_array($type, Stream::STREAM_TYPES, true)
        ) {
            return new JsonResponse([
                'status' => 'fail',
                'message' => 'The parameters streamId, action and type are mandatory',
            ], Response::HTTP_BAD_REQUEST);
        }

        $action = $streamAction === 'start' ? Stream::ACTION_START : Stream::ACTION_STOP;

        $stream = new StreamDTO($streamId, $type, $action);
        $this->commandBus->handle(new StreamCommand($happening, $stream));

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'stream handled',
        ]);
    }
}
