<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\CRM;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;

class RecordView
{
    public const TRANSLATION_KEY_TRACE_COMMERCIAL_STATUS = 'admin.sheet.details.crm.record.trace.set_commercial_status.';
    public const INTENTION_REMOVE_COMMENT = 'remove_comment';

    public const TRACE = 'trace';
    public const COMMENT = 'comment';

    /** @var Admin */
    public $author;

    /** @var string */
    public $comment;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var string */
    public $type;

    /** @var null|int */
    public $commentId;

    public function __construct(
        Admin $author,
        string $comment,
        string $type,
        \DateTimeInterface $createdAt,
        ?int $commentId = null
    ) {
        $this->author = $author;
        $this->comment = $comment;
        $this->createdAt = $createdAt;
        $this->type = $type;
        $this->commentId = $commentId;
    }

    /**
     * @return bool
     */
    public function isTrace(): bool
    {
        return self::TRACE === $this->type;
    }

    /**
     * @return bool
     */
    public function isComment(): bool
    {
        return self::COMMENT === $this->type;
    }

    /**
     * @return string
     */
    public function getTraceTranslationKey(): string
    {
        return self::TRANSLATION_KEY_TRACE_COMMERCIAL_STATUS;
    }

    /**
     * @return string
     */
    public function getCorrespondingLabel(): string
    {
        if (!$this->isTrace()) {
            return '';
        }

        if (isset(CommercialStatus::STATUS_WITH_LABEL[$this->comment])) {
            return CommercialStatus::STATUS_WITH_LABEL[$this->comment];
        }

        return 'default';
    }

    public function getRemoveIntentionComment(): string
    {
        return self::INTENTION_REMOVE_COMMENT;
    }
}
