<?php

namespace Proximum\Vimeet\Application\Command\Type\Content;

use Proximum\Vimeet\Domain\Model\Type\Content;
use Proximum\Vimeet\Domain\Repository\Type\ContentRepositoryInterface;

class DefineTermsOfSaleHandler
{
    /** @var ContentRepositoryInterface */
    private $contentRepository;

    public function __construct(ContentRepositoryInterface $contentRepository)
    {
        $this->contentRepository = $contentRepository;
    }

    public function handle(DefineTermsOfSale $defineTermsOfSale): void
    {
        if (!$defineTermsOfSale->enabled) {
            if ($defineTermsOfSale->content !== null) {
                $this->contentRepository->remove($defineTermsOfSale->content);
            }

            return;
        }

        if ($defineTermsOfSale->content instanceof Content) {
            $this->translateContent($defineTermsOfSale->content, $defineTermsOfSale->translations);

            $this->contentRepository->update($defineTermsOfSale->content);

            return;
        }

        $content = new Content($defineTermsOfSale->type, Content::TYPE_TERMS_OF_SALE);
        $this->translateContent($content, $defineTermsOfSale->translations);

        $this->contentRepository->add($content);
    }

    private function translateContent(Content $content, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            $content->translate($locale, $translation['value']);
        }
    }
}
