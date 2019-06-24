<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\FastCheckin;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CheckinActionsAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var ScanRepositoryInterface */
    private $scanRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        ScanRepositoryInterface $scanRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->scanRepository = $scanRepository;
        $this->dateTime = $dateTime;
    }

    public function __invoke(Event $event, User $user)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $checkinDate = $this->getUserCheckinDate($event, $user);

        return $this->engine->renderResponse(
            '@Admin/Event/checkinUser.html.twig',
            [
                'event' => $event,
                'user' => $user,
                'checkinDate' => $checkinDate,
            ]
        );
    }

    private function getUserCheckinDate(Event $event, User $user): ?\DateTime
    {
        $checkinDates = $this->scanRepository->getScanDateByUsersAndEvent([$user], $event, $this->dateTime);

        return isset($checkinDates[$user->getId()]) ? $checkinDates[$user->getId()]->getScannedAt() : null;
    }
}
