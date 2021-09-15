<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\ParticipateHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Happening\ParticipationVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HappeningParticipateAction
{
    /** @var ParticipateHandler */
    private $participateHandler;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        ParticipateHandler $participateHandler
    ) {
        $this->participateHandler = $participateHandler;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Happening   $happening
     * @param UserDomain  $userDomain
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Happening $happening,
        UserDomain $userDomain
    ): JsonResponse {
        $event = $eventDomain->getEvent();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->authorizationCheckerAdapter->isGranted(ParticipationVoter::PARTICIPATE, $sheet)
        ) {
            throw new AccessDeniedException('Access denied to this happening');
        }

        if ($happening->getEvent() !== $event || $sheet->getEvent() !== $event) {
            throw new NotFoundHttpException('Happening or sheet not in this event');
        }

        return $this->participateHandler->handle($request, $happening, $sheet, $userDomain->getUser());
    }
}
