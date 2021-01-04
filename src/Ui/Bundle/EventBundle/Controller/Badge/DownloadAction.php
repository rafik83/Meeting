<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Badge;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\EBadgePdfPrinter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadAction
{
    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EBadgePdfPrinter */
    private $eBadgePdfPrinter;

    public function __construct(
        UserEventTokenRepositoryInterface $userEventTokenRepository,
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        EBadgePdfPrinter $eBadgePdfPrinter
    ) {
        $this->userEventTokenRepository = $userEventTokenRepository;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->eBadgePdfPrinter = $eBadgePdfPrinter;
    }

    public function __invoke(Request $request, EventDomain $eventDomain, string $token, string $format): Response
    {
        $userEventToken = $this->userEventTokenRepository->findByTokenAndEventAndType(
            $token,
            $eventDomain->getEvent(),
            UserEventTokenType::EBADGE_CONFIRMATION
        );

        if (!$userEventToken instanceof UserEventToken) {
            throw new AccessDeniedException('Access denied');
        }

        $event = $eventDomain->getEvent();
        $user = $userEventToken->getUser();

        if ('pdf' === $format) {
            return new BinaryFileResponse(
                $this->eBadgePdfPrinter->generate($userEventToken)
            );
        }

        $badge = $this->queryBus->handle(new GetUserBadgeByEventQuery($event, $user));

        return $this->engine->renderResponse('@Event/Badge/Print/show.html.twig', [
            'userBadgeByEventView' => $badge,
        ]);
    }
}
