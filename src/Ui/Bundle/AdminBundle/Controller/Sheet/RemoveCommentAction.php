<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\View\Sheet\Details\CRM\RecordView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RemoveCommentAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommentRepositoryInterface */
    private $commentRepository;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommentRepositoryInterface $commentRepository,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->commentRepository = $commentRepository;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->flashBag = $flashBag;
    }

    public function __invoke(
        Request $request,
        Event $event,
        Sheet $sheet,
        Comment $comment
    ): RedirectResponse {
        if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')
            || !$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationChecker->isGranted('PERMISSION_SHEET_ACCESS', $sheet)
            || $sheet !== $comment->getSheet()
        ) {
            throw new AccessDeniedException();
        }

        $csrfToken = new CsrfToken(RecordView::INTENTION_REMOVE_COMMENT, $request->get('_token'));

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new AccessDeniedException('Invalid csrf token');
        }

        $this->commentRepository->remove($comment);
        $this->flashBag->add('success', 'flash.admin.sheet.comment.remove.success');

        return new RedirectResponse(
          $this->urlGenerator->generate('admin_sheet_details', [
              'event' => $event->getId(),
              'sheet' => $sheet->getId(),
          ])
        );
    }
}
