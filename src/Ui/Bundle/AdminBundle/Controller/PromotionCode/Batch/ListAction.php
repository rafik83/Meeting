<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\PromotionCode\Batch;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\PromotionCodeGroupRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ListAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    /** @var PromotionCodeGroupRepositoryInterface */
    private $promotionCodeGroupRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        PromotionCodeGroupRepositoryInterface $promotionCodeGroupRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->promotionCodeGroupRepository = $promotionCodeGroupRepository;
    }

    public function __invoke(Request $request, Event $event)
    {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        return new Response(
            $this->engine->render(
                '@Admin/PromotionCode/Batch/list.html.twig',
                [
                    'event' => $event,
                    'promotionCodeGroups' => $this->promotionCodeGroupRepository->findByEvent($event),
                ]
            )
        );
    }
}
