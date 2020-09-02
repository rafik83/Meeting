<?php

namespace Proximum\Vimeet\Application\Query\Chat\View;

class ChatMessageView
{
    /** @var int */
    public $id;

    /** @var string */
    public $content;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var null|string */
    public $formattedCreatedAt;

    /** If true user is author question
     * @var bool
     */
    public $isAuthor;

    /** @var int */
    public $authorId;

    /** @var string */
    public $authorName;

    /** @var string */
    public $sheetTitle;

    public function __construct(
        int $id,
        string $content,
        \DateTimeInterface $createdAt,
        int $authorId,
        string $authorName,
        string $sheetTitle
    ) {
        $this->id = $id;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->authorId = $authorId;
        $this->authorName = $authorName;
        $this->sheetTitle = $sheetTitle;
    }
}
