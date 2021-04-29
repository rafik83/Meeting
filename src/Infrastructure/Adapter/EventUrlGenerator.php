<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventUrlGenerator implements EventUrlGeneratorInterface
{
    /** @var string http|https */
    private $scheme;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(string $scheme, UrlGeneratorInterface $urlGenerator)
    {
        $this->scheme       = $scheme;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generateEventAbsoluteUrl(Event $event, $routeName, $parameters = [])
    {
        return sprintf(
            '%s://%s%s',
            $this->scheme,
            $event->getDomain(),
            $this->urlGenerator->generate($routeName, $parameters)
        );
    }

    public function generateImageAbsoluteUrl(Event $event, string $path)
    {
        return sprintf(
            '%s://%s%s',
            $this->scheme,
            $event->getDomain(),
            $path
        );
    }

    public function generateBaseEventAbsoluteUrl(Event $event): string
    {
        return sprintf(
            '%s://%s',
            $this->scheme,
            $event->getDomain()
        );
    }
}
