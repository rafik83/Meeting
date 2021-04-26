<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\CustomLink;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\CustomLink\Update;
use Proximum\Vimeet\Application\Command\Event\CustomLink\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event\CustomLink;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // fixtures

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $staticFormulation = $this->prophesize(StaticFormulation::class);
        $staticFormulation->getTitle('fr')->willReturn('titre');
        $staticFormulation->getTitle('en')->willReturn('title');

        $customLink = $this->prophesize(CustomLink::class);
        $customLink->getStaticFormulation()->willReturn($staticFormulation->reveal());
        $customLink->getTypes()->willReturn([$type1->reveal()]);
        $customLink->getUrl()->willReturn('https://example.org/1');
        $customLink->getIconName()->willReturn('prev-icon');
        $customLink->getIconColor()->willReturn('#aaaaaa');
        $customLink->getLabelColor()->willReturn('#bbbbbb');
        $customLink->getButtonColor()->willReturn('#cccccc');
        $customLink->getPriority()->willReturn(0);
        $customLink->update(
            ['fr' => ['title' => 'titre à jour'], 'en' => ['title' => 'updated title'],],
            [$type2->reveal()],
            'https://example.org/2',
            'loader-icon',
            '#111111',
            '#222222',
            '#333333',
            1
        )
            ->shouldBeCalled()
        ;

        // dependency's prophesizes

        $customLinkRepository = $this->prophesize(CustomLinkRepositoryInterface::class);
        $customLinkRepository->set($customLink->reveal())->shouldBeCalled();

        // run test

        $update = new Update($customLink->reveal(), ['fr', 'en',]);
        $update->iconName = 'loader-icon';
        $update->translatedLabels = ['fr' => ['title' => 'titre à jour'], 'en' => ['title' => 'updated title'],];
        $update->types = [$type2->reveal()];
        $update->url = 'https://example.org/2';
        $update->iconColor = '#111111';
        $update->labelColor = '#222222';
        $update->buttonColor = '#333333';
        $update->priority = 1;

        $handler = new UpdateHandler($customLinkRepository->reveal());
        $handler->handle($update);
    }
}
