<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;

class ContentManager
{
    /** @var ContentRepositoryInterface */
    private $contentRepository;

    /**
     * @param ContentRepositoryInterface $contentRepository
     */
    public function __construct(ContentRepositoryInterface $contentRepository)
    {
        $this->contentRepository = $contentRepository;
    }

    /**
     * @param Event $event
     *
     * @return Content
     */
    public function createTermsOfSale(Event $event)
    {
        $content = new Content($event, Content::TYPE_TERMS_OF_SALE);
        $content->translate('fr', 'foobar');

        $this->contentRepository->add($content);

        return $content;
    }
}
