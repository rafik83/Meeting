<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Networking;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Networking\PrivateChatQuery;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class PrivateChatAction
{
    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        QueryBusInterface $queryBus,
        Environment $twig,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EventOpenAccessChecker $eventOpenAccessChecker,
        NetworkingAccessChecker $networkingAccessChecker,
        UserRepositoryInterface $userRepository
    ) {
        $this->twig = $twig;
        $this->queryBus = $queryBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
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

        $chatView = $this->queryBus->handle(new PrivateChatQuery($sheet, $userDomain->getUser(), $toUser));

        return new Response(
            $this->twig->render(
                '@Event/Networking/privateChat.html.twig',
                [
                    'privateChatView' => $chatView,
                    'sheet' => $sheet,
                    'event' => $event,
                ]
            )
        );
    }
}
