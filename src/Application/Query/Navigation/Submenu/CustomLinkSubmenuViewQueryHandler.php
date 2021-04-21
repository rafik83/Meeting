<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Model\Event\CustomLink;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;

class CustomLinkSubmenuViewQueryHandler
{
    private CustomLinkRepositoryInterface $customLinkRepository;

    public function __construct(
        CustomLinkRepositoryInterface $customLinkRepository
    ){
        $this->customLinkRepository = $customLinkRepository;
    }

    public function handle(CustomLinkSubmenuViewQuery $query): ?array
    {

        $customLinks = $this->customLinkRepository->findByType(
            $query->sheet->getType()
        );

        return array_map(static function(CustomLink $customLink) use ($query): SubmenuButtonView {
            $locale = $query->locale;
            return new SubmenuButtonView(
                $customLink->getIconName(),
                $customLink->getLabel($locale),
                $customLink->getUrl(),
                false,
                null,
                false,
                [],
                $customLink->getIconColor(),
                $customLink->getLabelColor(),
                $customLink->getButtonColor(),

            );
        }, $customLinks);

    }
}
