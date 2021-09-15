<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\PromotionCode\Batch;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Query\PromotionCode\Batch\PromotionCodeGroupList\GetListView;
use Proximum\Vimeet\Application\Query\PromotionCode\Batch\PromotionCodeGroupList\GetListViewHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var Environment */
    private $twig;

    /** @var GetListViewHandler */
    private $getListViewHandler;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        Environment $twig,
        GetListViewHandler $getListViewHandler
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->twig = $twig;
        $this->getListViewHandler = $getListViewHandler;
    }

    public function __invoke(Request $request, Event $event)
    {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        return new Response(
            $this->twig->render(
                '@Admin/PromotionCode/Batch/list.html.twig',
                [
                    'event' => $event,
                    'promotionCodeGroupViews' => $this->getListViewHandler->handle(new GetListView($event)),
                ]
            )
        );
    }
}
