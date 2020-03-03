<?php

namespace Proximum\Vimeet\Application\Query\Contact;

class ContactListView
{
    /** @var array */
    private $contactPreviewViews;

    /** @var bool */
    private $isItDDay;

    /** @var bool */
    private $showCheckinStatus;

    public function __construct(bool $showCheckinStatus, bool $isItDDay, array $contactPreviewViews)
    {
        $this->showCheckinStatus = $showCheckinStatus;
        $this->isItDDay = $isItDDay;
        $this->contactPreviewViews = $contactPreviewViews;
    }

    public function getContactPreviewViews(): array
    {
        return $this->contactPreviewViews;
    }

    public function isItDDay(): bool
    {
        return $this->isItDDay;
    }

    public function showCheckinStatus(): bool
    {
        return $this->showCheckinStatus;
    }
}
