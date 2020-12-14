<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\DeleteHappeningQuestion;
use Proximum\Vimeet\Application\Exception\Happening\DeleteQuestionNotAllowedException;
use Proximum\Vimeet\Application\Exception\Happening\HappeningException;
use Proximum\Vimeet\Application\Exception\Happening\QuestionNotFoundException;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Happening\ParticipationVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DeleteHappeningQuestionAction
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
        EventDomain $eventDomain,
        Sheet $sheet,
        Happening $happening,
        UserDomain $userDomain
    ): JsonResponse
    {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->authorizationCheckerAdapter->isGranted(ParticipationVoter::PARTICIPATE, $sheet)
            || !$this->canAccessToWebinar->isSatisfiableBy($happening, $user)
            || $happening->getEvent() !== $event
            || $sheet->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied to this happening');
        }

        $data = json_decode($request->getContent(), true);
        $messageId = (int)($data['messageId'] ?? 0);

        if (!$messageId) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing id']);
        }

        try {
            $this->commandBus->handle(new DeleteHappeningQuestion($messageId, $user, $happening));
        } catch(QuestionNotFoundException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch(DeleteQuestionNotAllowedException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch(HappeningException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
