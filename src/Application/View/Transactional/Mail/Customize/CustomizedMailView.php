<?php

namespace Proximum\Vimeet\Application\View\Transactional\Mail\Customize;

use Proximum\Vimeet\Domain\Intention\IntentionType;

class CustomizedMailView
{
    /** @var string */
    public $key;

    /** @var string */
    public $subject;

    /** @var bool */
    public $isCustomizableByTypes;

    /** @var string[] */
    public $associatedTypeTitles;

    /** @var int */
    public $messageId;

    /** @var bool */
    public $isEnabled;

    public function __construct(
        int $messageId,
        string $key,
        string $subject,
        bool $isCustomizableByTypes,
        bool $isEnabled,
        array $associatedTypeTitles
    ) {
        $this->messageId = $messageId;
        $this->key = $key;
        $this->subject = $subject;
        $this->isCustomizableByTypes = $isCustomizableByTypes;
        $this->associatedTypeTitles = $associatedTypeTitles;
        $this->isEnabled = $isEnabled;
    }

    public function getIntention(): string
    {
        return IntentionType::INTENTION_REMOVE_CUSTOMIZED_MAIL;
    }
}
