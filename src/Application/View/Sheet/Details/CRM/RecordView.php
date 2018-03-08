<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details\CRM;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;

class RecordView
{
    const TRANSLATION_KEY_TRACE_COMMERCIAL_STATUS = 'admin.sheet.details.crm.record.trace.set_commercial_status.';

    const TRACE = 'trace';
    const COMMENT = 'comment';

    /** @var Admin */
    public $author;

    /** @var string */
    public $comment;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var string */
    public $type;

    /**
     * @param Admin              $author
     * @param string             $comment
     * @param string             $type
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Admin $author,
        string $comment,
        string $type,
        \DateTimeInterface $createdAt
    ) {
        $this->author = $author;
        $this->comment = $comment;
        $this->createdAt = $createdAt;
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isTrace(): bool
    {
        return $this->type === self::TRACE;
    }

    /**
     * @return bool
     */
    public function isComment(): bool
    {
        return $this->type === self::COMMENT;
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
}
