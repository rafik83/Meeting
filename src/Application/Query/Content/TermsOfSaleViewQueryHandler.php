<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Content;

use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Domain\View\Content\TermsOfSaleView;

class TermsOfSaleViewQueryHandler
{
    /** @var ContentRepositoryInterface */
    private $contentRepository;

    /** @var MarkdownAdapterInterface */
    private $markdownAdapter;

    /**
     * @param ContentRepositoryInterface $contentRepository
     * @param MarkdownAdapterInterface   $markdownAdapter
     */
    public function __construct(
        ContentRepositoryInterface $contentRepository,
        MarkdownAdapterInterface $markdownAdapter
    ) {
        $this->contentRepository = $contentRepository;
        $this->markdownAdapter   = $markdownAdapter;
    }

    /**
     * @param TermsOfSaleViewQuery $query
     *
     * @return TermsOfSaleView
     */
    public function handle(TermsOfSaleViewQuery $query): TermsOfSaleView
    {
        $termsOfSale = $this->contentRepository->findByEventAndType($query->event, Content::TYPE_TERMS_OF_SALE);
        $content     = $termsOfSale->getValue($query->locale, $query->event->getFallback());

        return new TermsOfSaleView($this->markdownAdapter->toHtml($content));
    }
}
