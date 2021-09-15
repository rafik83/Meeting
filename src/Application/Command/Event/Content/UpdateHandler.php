<?php

namespace Proximum\Vimeet\Application\Command\Event\Content;

use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;

class UpdateHandler
{
    /**
     * @var ContentRepositoryInterface
     */
    private $contentRepository;

    /**
     * @param ContentRepositoryInterface $contentRepository
     */
    public function __construct(ContentRepositoryInterface $contentRepository)
    {
        $this->contentRepository = $contentRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        foreach ($update->translations as $locale => $value) {
            $update->content->translate($locale, $value['value']);
        }

        $this->contentRepository->set($update->content);
    }
}
