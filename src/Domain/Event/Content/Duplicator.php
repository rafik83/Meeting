<?php

namespace Proximum\Vimeet\Domain\Event\Content;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;

class Duplicator
{
    /**
     * @var ContentRepositoryInterface
     */
    private $contentRepository;

    /**
     * Duplicator constructor.
     *
     * @param ContentRepositoryInterface $contentRepository
     */
    public function __construct(ContentRepositoryInterface $contentRepository)
    {
        $this->contentRepository = $contentRepository;
    }

    /**
     * @param Event  $event
     * @param string $type
     */
    public function duplicate(Event $event, string $type)
    {
        $content = $this->contentRepository->findByEventAndType($event, $type);
        $contentDuplicatedFrom = $this
            ->contentRepository
            ->findByEventAndType($event->getDuplicatedFrom(), $type)
        ;

        if (null !== $contentDuplicatedFrom) {
            foreach ($contentDuplicatedFrom->getEvent()->getLocales() as $locale) {
                $content->translate($locale, $contentDuplicatedFrom->getValue($locale));
            }
        }

        $this->contentRepository->set($content);
    }
}
