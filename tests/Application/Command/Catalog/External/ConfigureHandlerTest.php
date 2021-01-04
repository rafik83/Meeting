<?php

namespace Application\Command\Catalog\External;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Catalog\CatalogTagFilterHandler;
use Proximum\Vimeet\Application\Command\Catalog\External\Configure;
use Proximum\Vimeet\Application\Command\Catalog\External\ConfigureHandler;
use Proximum\Vimeet\Application\Command\Catalog\External\SetSearchFacet;
use Proximum\Vimeet\Application\Command\Catalog\External\SetSearchFacetHandler;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilterTranslation;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class ConfigureHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $catalogVisibilityRepository;

    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $setSearchFacetHandler;

    /** @var ObjectProphecy */
    private $event;

    /** @var array */
    private $searchFacets;

    /** @var array */
    private $persistedCatalogTagFilter;

    /** @var ObjectProphecy */
    private $catalogTagFilterHandler;

    public function setUp()
    {
        $this->catalogVisibilityRepository = $this->prophesize(CatalogVisibilityRepositoryInterface::class);
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->setSearchFacetHandler = $this->prophesize(SetSearchFacetHandler::class);
        $this->catalogTagFilterHandler = $this->prophesize(CatalogTagFilterHandler::class);
        $this->event = $this->prophesize(Event::class);

        $this->searchFacets = [
            'type' => [
                'enabled' => true,
                'translations' => [
                    'fr' => [
                        'type' => 'type',
                        'label' => 'Type',
                        'placeholder' => '',
                    ],
                    'en' => [
                        'type' => 'type',
                        'label' => 'Type',
                        'placeholder' => '',
                    ],
                ],
            ],
            'category' => [
                'enabled' => false,
                'translations' => [
                    'fr' => [
                        'type' => 'category',
                        'label' => 'Category',
                        'placeholder' => '',
                    ],
                    'en' => [
                        'type' => 'category',
                        'label' => 'Category',
                        'placeholder' => '',
                    ],
                ],
            ],
            'position' => [
                'enabled' => true,
                'translations' => [
                    'fr' => [
                        'type' => 'position',
                        'label' => 'Position',
                        'placeholder' => 'Position placeholder fr',
                    ],
                    'en' => [
                        'type' => 'position',
                        'label' => 'Position',
                        'placeholder' => 'Position placeholder en',
                    ],
                ],
            ],
            'structure' => [
                'enabled' => true,
                'translations' => [
                    'fr' => [
                        'type' => 'structure',
                        'label' => 'Structure',
                        'placeholder' => 'Structure placeholder fr',
                    ],
                    'en' => [
                        'type' => 'structure',
                        'label' => 'Structure',
                        'placeholder' => 'Structure placeholder en',
                    ],
                ],
            ],
            'localization' => [
                'enabled' => false,
                'translations' => [
                    'fr' => [
                        'type' => 'localization',
                        'label' => 'Localization',
                        'placeholder' => 'Localization placeholder fr',
                    ],
                    'en' => [
                        'type' => 'localization',
                        'label' => 'Localization',
                        'placeholder' => 'Localization placeholder en',
                    ],
                ],
            ],
            'keywords' => [
                'enabled' => false,
                'translations' => [
                    'fr' => [
                        'type' => 'keywords',
                        'label' => 'Keywords',
                        'placeholder' => 'Keywords placeholder fr',
                    ],
                    'en' => [
                        'type' => 'keywords',
                        'label' => 'Keywords',
                        'placeholder' => 'Keywords placeholder en',
                    ],
                ],
            ],
        ];

        $ct1 = new CatalogTagFilter($this->event->reveal(), 'sheet_title', 'external');
        $ct1->addTranslation(new CatalogTagFilterTranslation('fr', 'label', 'placeholder'));

        $ct2 = new CatalogTagFilter($this->event->reveal(), 'sheet_description', 'external');
        $ct2->addTranslation(new CatalogTagFilterTranslation('fr', 'label2', 'placeholder2'));

        $this->persistedCatalogTagFilter = [$ct1, $ct2];

        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->event->isExternalCatalogEnabled()->willReturn(false);
    }

    public function testHandle()
    {
        $catalogVisibility = new CatalogVisibility($this->event->reveal());

        $sf1 = new SearchFacet($this->event->reveal(), 'structure', true);
        $sf2 = new SearchFacet($this->event->reveal(), 'type', false);
        $sf3 = new SearchFacet($this->event->reveal(), 'keywords', true);

        $persistedSearchFacets = [$sf1, $sf2, $sf3];

        $command = new Configure($this->event->reveal(), $catalogVisibility, $persistedSearchFacets, $this->persistedCatalogTagFilter);

        $command->externalCatalogEnabled = true;
        $command->searchFacets = $this->searchFacets;

        $this
            ->catalogVisibilityRepository
            ->getByEvent($this->event)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->catalogVisibilityRepository->add($catalogVisibility)->shouldBeCalled();

        $this->setSearchFacetHandler
            ->handle(new SetSearchFacet($this->event->reveal(), $this->searchFacets, $persistedSearchFacets))
            ->shouldBeCalled()
        ;

        $this->catalogTagFilterHandler->handle($command)->shouldBeCalled();

        $this->event->setExternalCatalog(true)->shouldBeCalled();
        $this->eventRepository->set($this->event->reveal())->shouldBeCalled();

        $handler = new ConfigureHandler(
            $this->catalogVisibilityRepository->reveal(),
            $this->eventRepository->reveal(),
            $this->setSearchFacetHandler->reveal(),
            $this->catalogTagFilterHandler->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleWithExistingCatalogVisibility()
    {
        $catalogVisibility = new CatalogVisibility($this->event->reveal());
        $persistedSearchFacets = [
            new SearchFacet($this->event->reveal(), 'structure', true),
            new SearchFacet($this->event->reveal(), 'type', false),
            new SearchFacet($this->event->reveal(), 'keywords', true),
        ];

        $command = new Configure($this->event->reveal(), $catalogVisibility, $persistedSearchFacets, $this->persistedCatalogTagFilter);
        $command->externalCatalogEnabled = false;
        $command->searchFacets = $this->searchFacets;

        $this
            ->catalogVisibilityRepository
            ->getByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($catalogVisibility);

        $this->catalogVisibilityRepository->set($catalogVisibility)->shouldBeCalled();

        $this->setSearchFacetHandler
            ->handle(new SetSearchFacet($this->event->reveal(), $this->searchFacets, $persistedSearchFacets))
            ->shouldBeCalled()
        ;

        $this->catalogTagFilterHandler->handle($command)->shouldBeCalled();

        $this->event->setExternalCatalog(false)->shouldBeCalled();
        $this->eventRepository->set($this->event->reveal())->shouldBeCalled();

        $handler = new ConfigureHandler(
            $this->catalogVisibilityRepository->reveal(),
            $this->eventRepository->reveal(),
            $this->setSearchFacetHandler->reveal(),
            $this->catalogTagFilterHandler->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleAddRegistrationUrl()
    {
        $catalogVisibility = $this->prophesize(CatalogVisibility::class);
        $catalogVisibility->getTypes()->willReturn([]);
        $catalogVisibility->getCategories()->willReturn([]);
        $catalogVisibility->hasMessage()->willReturn(false);
        $catalogVisibility->getRegistrationUrl()->willReturn('');
        $catalogVisibility->getMessage('fr')->willReturn(null);
        $catalogVisibility->getMessage('en')->willReturn(null);

        $persistedSearchFacets = [
            new SearchFacet($this->event->reveal(), 'structure', true),
            new SearchFacet($this->event->reveal(), 'type', false),
            new SearchFacet($this->event->reveal(), 'keywords', true),
        ];

        $command = new Configure(
            $this->event->reveal(),
            $catalogVisibility->reveal(),
            $persistedSearchFacets,
            $this->persistedCatalogTagFilter
        );

        $command->externalCatalogEnabled = false;
        $command->searchFacets = $this->searchFacets;

        $command->registrationUrl = 'https://www.google.com';
        $command->hasMessage = false;
        $command->messageTranslations = [];

        $catalogVisibility->updateTypesAndCategories([], [])->shouldBeCalled();
        $catalogVisibility->enableMessage(false)->shouldBeCalled();
        $catalogVisibility->setRegistrationUrl('https://www.google.com')->shouldBeCalled();

        $this
            ->catalogVisibilityRepository
            ->getByEvent($this->event)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->catalogVisibilityRepository->add($catalogVisibility->reveal())->shouldBeCalled();

        $this->catalogTagFilterHandler->handle($command)->shouldBeCalled();

        $this->setSearchFacetHandler->handle(
            new SetSearchFacet($this->event->reveal(), $this->searchFacets, $persistedSearchFacets)
        )->shouldBeCalled();

        $this->event->setExternalCatalog(false)->shouldBeCalled();
        $this->eventRepository->set($this->event->reveal())->shouldBeCalled();

        $handler = new ConfigureHandler(
            $this->catalogVisibilityRepository->reveal(),
            $this->eventRepository->reveal(),
            $this->setSearchFacetHandler->reveal(),
            $this->catalogTagFilterHandler->reveal()
        );

        $handler->handle($command);
    }
}
