<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail\CRM;

use Proximum\Vimeet\Application\View\Sheet\Details\CRM\RecordView;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;

class RecordViewsQueryHandler
{
    /** @var TraceRepositoryInterface */
    private $traceRepository;

    /** @var CommentRepositoryInterface */
    private $commentRepository;

    /**
     * @param TraceRepositoryInterface   $traceRepository
     * @param CommentRepositoryInterface $commentRepository
     */
    public function __construct(
        TraceRepositoryInterface $traceRepository,
        CommentRepositoryInterface $commentRepository
    ) {
        $this->traceRepository = $traceRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @param RecordViewsQuery $query
     *
     * @return array
     */
    public function handle(RecordViewsQuery $query): array
    {
        $recordViews = [];

        $comments = $this->commentRepository->getCommentsBySheet($query->sheet);
        $traces = $this->traceRepository->getAllTracesByObjectAndAction($query->sheet, Trace::SET_COMMERCIAL_STATUS);

        foreach ($comments as $comment) {
            $recordViews[] = new RecordView(
                $comment->getAuthor(),
                $comment->getText(),
                RecordView::COMMENT,
                $comment->getCreatedAt(),
                $comment->getId()
            );
        }

        foreach ($traces as $trace) {
            $recordViews[] = new RecordView(
                $trace->getAdmin(),
                $trace->getComment(),
                RecordView::TRACE,
                $trace->getDate()
            );
        }

        usort($recordViews, function (RecordView $previous, RecordView $next) {
            return $next->createdAt <=> $previous->createdAt;
        });

        return $recordViews;
    }
}
