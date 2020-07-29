<?php

namespace Proximum\Vimeet\Application\View\Sheet\Preview;

use Proximum\Vimeet\Application\Components\Sheet\Preview\CustomPreviewData;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Template\AbstractChild;

class PreviewView
{
    /** @var string */
    public $id;

    /** @var string */
    public $content;

    /** @var string */
    public $type;

    /** @var bool */
    public $strong = false;

    /** @var CardView[] */
    public $cardViews;

    /** @var bool */
    public $link = false;

    /** @var TagView[] */
    public $tagViews;

    /** @var string */
    public $populatedFromTag;

    /** @var bool */
    public $canDisplayImage;

    /** @var string|null */
    public $fileMimeType;

    public function __construct(
        string $id,
        string $content,
        string $type,
        array $cardViews = [],
        bool $link = false,
        bool $canDisplayImage = true,
        ?string $fileMimeType = null
    ) {
        $this->id = $id;
        $this->content = $content;
        $this->type = $type;
        $this->cardViews = $cardViews;
        $this->tagViews = [];
        $this->link = $link;
        $this->canDisplayImage = $canDisplayImage;
        $this->fileMimeType = $fileMimeType;
    }

    public function isImage(): bool
    {
        return AbstractChild::TEMPLATE_OBJECT_TYPE_IMAGE === $this->type;
    }

    public function isParticipant(): bool
    {
        return AbstractChild::TEMPLATE_OBJECT_TYPE_PARTICIPANT === $this->type;
    }

    public function isVideo(): bool
    {
        return AbstractChild::TEMPLATE_OBJECT_TYPE_VIDEO === $this->type;
    }

    public function hasContent(): bool
    {
        return !empty($this->content);
    }

    public function isTag(): bool
    {
        return AbstractChild::TEMPLATE_OBJECT_TYPE_TAG === $this->type;
    }

    public function isParticipantsPosition(): bool
    {
        return CustomPreviewData::PARTICIPANTS_POSITION === $this->type;
    }

    public function isStrong(): bool
    {
        return false !== $this->strong;
    }

    public function isPopulatedFromTagSheetOrganization(): bool
    {
        return Tag::SHEET_ORGANIZATION === $this->populatedFromTag;
    }

    public function addTagView(TagView $tagView): void
    {
        $this->tagViews[] = $tagView;
    }
}
