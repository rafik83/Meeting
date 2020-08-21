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

    public function __construct(int $id, string $content, \DateTimeInterface $createdAt)
    {
        $this->id = $id;
        $this->content = $content;
        $this->createdAt = $createdAt;
    }
}
