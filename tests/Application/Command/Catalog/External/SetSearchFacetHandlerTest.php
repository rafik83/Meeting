<?php

namespace Application\Command\Catalog\External;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Catalog\External\SetSearchFacet;
use Proximum\Vimeet\Application\Command\Catalog\External\SetSearchFacetHandler;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;

class SetSearchFacetHandlerTest extends TestCase
{
    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    public function setUp()
    {
        $this->searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);
    }

    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getLocales()->willReturn(['fr', 'en']);

        $persistedSearchFacets = [
            new SearchFacet($event->reveal(), 'structure', true),
            new SearchFacet($event->reveal(), 'type', false),
            new SearchFacet($event->reveal(), 'keywords', true),
        ];

        $searchFacets = [
            'type' => [
                'enabled' => true,
                'translations' => [
                    'fr' => [
                        'label' => 'Type',
                        'placeholder' => '',
                    ],
                    'en' => [
                        'label' => 'Type',
                        'placeholder' => '',
                    ],
                ],
            ],
            'category' => [
                'enabled' => false,
                'translations' => [
                    'fr' => [
                        'label' => 'Category',
                        'placeholder' => '',
                    ],
                    'en' => [
                        'label' => 'Category',
                        'placeholder' => '',
                    ],
                ],
            ],
            'position' => [
                'enabled' => true,
                'translations' => [
                    'fr' => [
                        'label' => 'Position',
                        'placeholder' => 'Position placeholder fr',
                    ],
                    'en' => [
                        'label' => 'Position',
                        'placeholder' => 'Position placeholder en',
                    ],
                ],
            ],
            'structure' => [
                'enabled' => true,
                'translations' => [
                    'fr' => [
                        'label' => 'Structure',
                        'placeholder' => 'Structure placeholder fr',
                    ],
                    'en' => [
                        'label' => 'Structure',
                        'placeholder' => 'Structure placeholder en',
                    ],
                ],
            ],
            'localization' => [
                'enabled' => false,
                'translations' => [
                    'fr' => [
                        'label' => 'Localization',
                        'placeholder' => 'Localization placeholder fr',
                    ],
                    'en' => [
                        'label' => 'Localization',
                        'placeholder' => 'Localization placeholder en',
                    ],
                ],
            ],
            'keywords' => [
                'enabled' => false,
                'translations' => [
                    'fr' => [
                        'label' => 'Keywords',
                        'placeholder' => 'Keywords placeholder fr',
                    ],
                    'en' => [
                        'label' => 'Keywords',
                        'placeholder' => 'Keywords placeholder en',
                    ],
                ],
            ],
        ];

        $command = new SetSearchFacet($event->reveal(), $searchFacets, $persistedSearchFacets);

        $expectedFacetOne   = new SearchFacet($event->reveal(), 'structure', true);
        $expectedFacetTwo   = new SearchFacet($event->reveal(), 'type', true);
        $expectedFacetThree = new SearchFacet($event->reveal(), 'keywords', false);
        $expectedFacetOther1 = new SearchFacet($event->reveal(), 'category', false);
        $expectedFacetOther2 = new SearchFacet($event->reveal(), 'localization', false);
        $expectedFacetOther3 = new SearchFacet($event->reveal(), 'position', true);
        $expectedFacetOne->translate('en', 'Structure', 'Structure placeholder en');
        $expectedFacetOne->translate('fr', 'Structure', 'Structure placeholder fr');
        $expectedFacetTwo->translate('en', 'Type', '');
        $expectedFacetTwo->translate('fr', 'Type', '');
        $expectedFacetThree->translate('en', 'Keywords', 'Keywords placeholder en');
        $expectedFacetThree->translate('fr', 'Keywords', 'Keywords placeholder fr');
        $expectedFacetOther1->translate('en', 'Category', '');
        $expectedFacetOther1->translate('fr', 'Category', '');
        $expectedFacetOther2->translate('en', 'Localization', 'Localization placeholder en');
        $expectedFacetOther2->translate('fr', 'Localization', 'Localization placeholder fr');
        $expectedFacetOther3->translate('en', 'Position', 'Position placeholder en');
        $expectedFacetOther3->translate('fr', 'Position', 'Position placeholder fr');

        $searchFacetRepository = $this->prophesize(SearchFacetRepositoryInterface::class);
        $searchFacetRepository->set(Argument::that(function (SearchFacet $searchFacet) use ($expectedFacetOne) {
            return $searchFacet->getType() === $expectedFacetOne->getType()
                && $searchFacet->isEnabled() === $expectedFacetOne->isEnabled()
                && $searchFacet->getLabel('fr') === $expectedFacetOne->getLabel('fr')
                && $searchFacet->getPlaceholder('fr') === $expectedFacetOne->getPlaceholder('fr')
                && $searchFacet->getLabel('en') === $expectedFacetOne->getLabel('en')
                && $searchFacet->getPlaceholder('en') === $expectedFacetOne->getPlaceholder('en')
                ;
        }))->shouldBeCalled();
        $searchFacetRepository->set(Argument::that(function (SearchFacet $searchFacet) use ($expectedFacetTwo) {
            return $searchFacet->getType() === $expectedFacetTwo->getType()
                && $searchFacet->isEnabled() === $expectedFacetTwo->isEnabled()
                && $searchFacet->getLabel('fr') === $expectedFacetTwo->getLabel('fr')
                && $searchFacet->getPlaceholder('fr') === $expectedFacetTwo->getPlaceholder('fr')
                && $searchFacet->getLabel('en') === $expectedFacetTwo->getLabel('en')
                && $searchFacet->getPlaceholder('en') === $expectedFacetTwo->getPlaceholder('en')
                ;
        }))->shouldBeCalled();
        $searchFacetRepository->set(Argument::that(function (SearchFacet $searchFacet) use ($expectedFacetThree) {
            return $searchFacet->getType() === $expectedFacetThree->getType()
                && $searchFacet->isEnabled() === $expectedFacetThree->isEnabled()
                && $searchFacet->getLabel('fr') === $expectedFacetThree->getLabel('fr')
                && $searchFacet->getPlaceholder('fr') === $expectedFacetThree->getPlaceholder('fr')
                && $searchFacet->getLabel('en') === $expectedFacetThree->getLabel('en')
                && $searchFacet->getPlaceholder('en') === $expectedFacetThree->getPlaceholder('en')
                ;
        }))->shouldBeCalled();

        $searchFacetRepository->add(Argument::that(function (SearchFacet $searchFacet) use ($expectedFacetOther1) {
            return $searchFacet->getType() === $expectedFacetOther1->getType()
                && $searchFacet->isEnabled() === $expectedFacetOther1->isEnabled()
                && $searchFacet->getLabel('fr') === $expectedFacetOther1->getLabel('fr')
                && $searchFacet->getPlaceholder('fr') === $expectedFacetOther1->getPlaceholder('fr')
                && $searchFacet->getLabel('en') === $expectedFacetOther1->getLabel('en')
                && $searchFacet->getPlaceholder('en') === $expectedFacetOther1->getPlaceholder('en')
                ;
        }))->shouldBeCalled();
        $searchFacetRepository->add(Argument::that(function (SearchFacet $searchFacet) use ($expectedFacetOther2) {
            return $searchFacet->getType() === $expectedFacetOther2->getType()
                && $searchFacet->isEnabled() === $expectedFacetOther2->isEnabled()
                && $searchFacet->getLabel('fr') === $expectedFacetOther2->getLabel('fr')
                && $searchFacet->getPlaceholder('fr') === $expectedFacetOther2->getPlaceholder('fr')
                && $searchFacet->getLabel('en') === $expectedFacetOther2->getLabel('en')
                && $searchFacet->getPlaceholder('en') === $expectedFacetOther2->getPlaceholder('en')
                ;
        }))->shouldBeCalled();
        $searchFacetRepository->add(Argument::that(function (SearchFacet $searchFacet) use ($expectedFacetOther3) {
            return $searchFacet->getType() === $expectedFacetOther3->getType()
                && $searchFacet->isEnabled() === $expectedFacetOther3->isEnabled()
                && $searchFacet->getLabel('fr') === $expectedFacetOther3->getLabel('fr')
                && $searchFacet->getPlaceholder('fr') === $expectedFacetOther3->getPlaceholder('fr')
                && $searchFacet->getLabel('en') === $expectedFacetOther3->getLabel('en')
                && $searchFacet->getPlaceholder('en') === $expectedFacetOther3->getPlaceholder('en')
                ;
        }))->shouldBeCalled();

        $handler = new SetSearchFacetHandler($searchFacetRepository->reveal());
        $handler->handle($command);
    }
}
