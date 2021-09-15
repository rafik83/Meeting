<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Networking;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Networking\RequestVisio;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RequestVisioAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        CommandBusInterface $commandBus,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        NetworkingAccessChecker $networkingAccessChecker,
        UserRepositoryInterface $userRepository
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->networkingAccessChecker = $networkingAccessChecker;
        $this->userRepository = $userRepository;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        ?User $toUser
    ) {
        if (null === $toUser) {
            $toUserId = $request->query->getInt('toUser');
            if (!$toUserId) {
                throw new BadRequestHttpException();
            }
            $toUser = $this->userRepository->findOneById($toUserId);
            if (!$toUser) {
                throw new NotFoundHttpException();
            }
        }

        $event = $eventDomain->getEvent();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->networkingAccessChecker->isSheetAllowedToAccess($sheet)
            || $sheet->getEvent()->getId() !== $event->getId()
        ) {
            throw new AccessDeniedException();
        }

        $this->commandBus->handle(new RequestVisio($sheet, $userDomain->getUser(), $toUser));

        return new JsonResponse(['status' => 'ok']);
    }
}
