<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Networking;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Networking\PrivateChatQuery;
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
use Symfony\Component\Templating\EngineInterface;

class VisioAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        NetworkingAccessChecker $networkingAccessChecker,
        UserRepositoryInterface $userRepository
    ) {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
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

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();

        if (!$this->networkingAccessChecker->allowedToAccess($event)) {
            throw new AccessDeniedException();
        }

        // todo
        // $visioView = $this->queryBus->handle(new VisioQuery($sheet, $userDomain->getUser(), $toUser));

        return new Response(
            $this->engine->render(
                '@Event/Networking/visio.html.twig',
                [
                    'visioView' => $visioView,
                    'sheet' => $sheet,
                    'event' => $event,
                ]
            )
        );
    }
}
