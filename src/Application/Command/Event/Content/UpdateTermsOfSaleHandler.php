<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Content;

use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;

class UpdateTermsOfSaleHandler
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

    public function handle(UpdateTermsOfSale $updateTermsOfSale)
    {
        $content = $updateTermsOfSale->content;

        foreach ($updateTermsOfSale->translations as $locale => $value) {
            $content->translate($locale, $value['value']);
        }

        $this->contentRepository->set($content);
    }
}
