<?php

namespace Proximum\Vimeet\Domain\Model\Event;

class LocalizedCustomLinkUrl
{
    private ?int $id;

    private CustomLink $customLink;

    private string $locale;

    private string $url;

    public function __construct(CustomLink $customLink, string $locale, string $url)
    {
        $this->customLink = $customLink;
        $this->locale = $locale;
        $this->url = $url;
    }

    public function update(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
