<?php

namespace Proximum\Vimeet\Application\View\Transactional\Mail;

use Proximum\Vimeet\Application\View\Transactional\Mail\Customize\CustomizedMailView;
use Proximum\Vimeet\Application\View\Transactional\Mail\Generic\GenericMailView;
use function count;

class MailView
{
    /** @var GenericMailView */
    public $genericMailView;

    /** @var CustomizedMailView[] */
    public $customizedMailViews;

    /** @var string */
    public $key;

    /** @var bool */
    public $isCustomizableByType;

    public function __construct(
        string $key,
        bool $isCustomizableByType,
        GenericMailView $genericMailView,
        array $customizedMailViews = []
    ) {
        $this->genericMailView = $genericMailView;
        $this->customizedMailViews = $customizedMailViews;
        $this->key = $key;
        $this->isCustomizableByType = $isCustomizableByType;
    }

    public function getNumberOfElements(): int
    {
        return count($this->customizedMailViews) + 1;
    }

    public function isGenericCustomizable(): bool
    {
        if (!$this->isCustomizableByType && !empty($this->customizedMailViews)) {
            return false;
        }

        if ($this->isCustomizableByType && empty($this->genericMailView->associatedTypeTitles)) {
            return false;
        }

        return true;
    }
}
