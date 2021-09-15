<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\FastCheckin;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class CheckinActionsAction
{
    /** @var Environment */
    private $twig;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var ScanRepositoryInterface */
    private $scanRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        Environment $twig,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        ScanRepositoryInterface $scanRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->twig = $twig;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->scanRepository = $scanRepository;
        $this->dateTime = $dateTime;
    }

    public function __invoke(Event $event, User $user)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$event->isAccessControlEnabled()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $checkinDate = $this->getUserCheckinDate($event, $user);

        return new Response($this->twig->render(
            '@Admin/Event/checkinUser.html.twig',
            [
                'event' => $event,
                'user' => $user,
                'checkinDate' => $checkinDate,
            ]
        ));
    }

    private function getUserCheckinDate(Event $event, User $user): ?\DateTime
    {
        $checkinDates = $this->scanRepository->getScanDateByUsersAndEvent([$user], $event, $this->dateTime);

        return isset($checkinDates[$user->getId()]) ? $checkinDates[$user->getId()]->getScannedAt() : null;
    }
}
