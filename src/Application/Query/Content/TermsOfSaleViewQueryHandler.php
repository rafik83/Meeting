<?php

namespace Proximum\Vimeet\Application\Query\Content;

use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Domain\Model\Type\Content as TypeContent;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Type\ContentRepositoryInterface as TypeContentRepositoryInterface;
use Proximum\Vimeet\Domain\View\Content\TermsOfSaleView;

class TermsOfSaleViewQueryHandler
{
    /** @var ContentRepositoryInterface */
    private $contentRepository;

    /** @var TypeContentRepositoryInterface */
    private $typeContentRepository;

    /** @var MarkdownAdapterInterface */
    private $markdownAdapter;

    public function __construct(
        ContentRepositoryInterface $contentRepository,
        TypeContentRepositoryInterface $typeContentRepository,
        MarkdownAdapterInterface $markdownAdapter
    ) {
        $this->contentRepository = $contentRepository;
        $this->markdownAdapter = $markdownAdapter;
        $this->typeContentRepository = $typeContentRepository;
    }

    public function handle(TermsOfSaleViewQuery $query): TermsOfSaleView
    {
        $content = $this->typeContentRepository->findByTypeAndAssociatedParticipationType(
            TypeContent::TYPE_TERMS_OF_SALE,
            $query->sheet->getType()
        );

        if ($content instanceof TypeContent) {
            return new TermsOfSaleView($content->getValue($query->locale));
        }

        $termsOfSale = $this->contentRepository->findByEventAndType($query->event, Content::TYPE_TERMS_OF_SALE);
        $content = $termsOfSale->getValue($query->locale, $query->event->getFallback());

        return new TermsOfSaleView($this->markdownAdapter->toHtml($content));
    }
}
