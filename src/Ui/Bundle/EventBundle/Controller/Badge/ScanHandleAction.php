<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Contact\Add;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierToUserQuery;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\User\Sheet\HasAccessToSheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Contact\ShowAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScanHandleAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var HasAccessToSheet */
    private $hasAccessToSheet;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus,
        HasAccessToSheet $hasAccessToSheet,
        QueryBusInterface $queryBus,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
        $this->hasAccessToSheet = $hasAccessToSheet;
        $this->queryBus = $queryBus;
        $this->router = $router;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    ): Response {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->hasAccessToSheet->isSatisfiedBy($user, $event, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['identifier'])) {
            return new JsonResponse('Bad parameters', 400);
        }

        $contact = $this->queryBus->handle(
            new QRCodeIdentifierToUserQuery((string) $data['identifier'])
        );

        if (!$contact instanceof User) {
            return new JsonResponse('User not found', 404);
        }

        $this->commandBus->handle(new Add($event, $user, $contact, Contact::ORIGIN_SCAN));

        return new JsonResponse(
            [
                'url' => $this->router->generate(
                    'event_contact_show',
                    [
                        'sheet'                    => $sheet->getId(),
                        'contactUser'              => $contact->getId(),
                        ShowAction::MODE_QUERY_KEY => ShowAction::MODE_EDIT_EVALUATION,
                    ]
                ),
            ]
        );
    }
}
