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
    public $isAuthor = false;

    /** @var string|null */
    public $avatar;

    /** @var int */
    public $authorId;

    /** @var string */
    public $authorName;

    /** @var string */
    public $sheetTitle;

    /** @var array */
    public $votes = [];

    public function __construct(
        int $id,
        string $content,
        \DateTimeInterface $createdAt,
        ?string $avatar,
        int $authorId,
        string $authorName,
        string $sheetTitle
    ) {
        $this->id = $id;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->avatar = $avatar;
        $this->authorId = $authorId;
        $this->authorName = $authorName;
        $this->sheetTitle = $sheetTitle;
    }
}
