<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\Webinar;


use Doctrine\Common\Util\Debug;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\MuteCommand;
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

class MuteAction
{
    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    private CanAccessToWebinar $canAccessToWebinar;
    private CommandBusInterface $commandBus;

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
            || !$happening->hasSpeaker($user)
        ) {
            throw new AccessDeniedException('Access denied to this happening');
        }

        $arrayResponse = json_decode($request->getContent(), true);

        $userId = $arrayResponse['userIdToMute'];

        if (null === $userId) {
            return new JsonResponse([
                'status' => 'fail',
                'message' => 'The parameter userId is mandatory',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->commandBus->handle(new MuteCommand($happening, $userId));

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'user muted',
        ]);
    }
}
