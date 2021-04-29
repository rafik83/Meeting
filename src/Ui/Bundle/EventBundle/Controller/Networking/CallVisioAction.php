<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Networking;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Contact\Add;
use Proximum\Vimeet\Application\Command\Networking\JoinVisio;
use Proximum\Vimeet\Application\Query\Networking\CallVisioQuery;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\CommandBus;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class CallVisioAction
{
    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    /** @var UserRepositoryInterface */
    private $userRepository;
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(
        QueryBusInterface $queryBus,
        Environment $twig,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        NetworkingAccessChecker $networkingAccessChecker,
        UserRepositoryInterface $userRepository,
        CommandBus $commandBus
    ) {
        $this->twig = $twig;
        $this->queryBus = $queryBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->networkingAccessChecker = $networkingAccessChecker;
        $this->userRepository = $userRepository;
        $this->commandBus = $commandBus;
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

        $user = $userDomain->getUser();

        $this->commandBus->handle(new Add($event, $user, $toUser, Contact::ORIGIN_PRIVATE_CHAT_VISIO));
        $this->commandBus->handle(new JoinVisio($sheet, $user, $toUser));

        $locale = $request->getLocale();

        $visioView = $this->queryBus->handle(new CallVisioQuery($sheet, $user, $toUser, $locale));

        return new Response(
            $this->twig->render(
                '@Event/CallVisio/CallVisio.html.twig',
                [
                    'visioView' => $visioView,
                    'sheet' => $sheet,
                    'event' => $event,
                ]
            )
        );
    }
}
