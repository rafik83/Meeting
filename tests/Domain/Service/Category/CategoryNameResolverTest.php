<?php

namespace Proximum\Vimeet\Tests\Domain\Service\Category;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;

class CategoryNameResolverTest extends TestCase
{
    public function testResolveWithTypeWithNoPosition()
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $sheet1->getType()->willReturn($type1->reveal());
        $sheet2->getType()->willReturn($type1->reveal());
        $sheet3->getType()->willReturn($type2->reveal());
        $category1 = $this->prophesize(Category::class);
        $category2 = $this->prophesize(Category::class);
        $category2->getTitle('fr')->shouldBeCalled()->willReturn('Category 2');
        $category1->getTitle('fr')->shouldNotBeCalled();

        $type1->getId()->willReturn(12);
        $type2->getId()->willReturn(10);
        $type1->getPosition()->willReturn(1);
        $type2->getPosition()->willReturn(null);
        $type1->getCategories()->willReturn(new ArrayCollection([$category1->reveal()]));
        $type2->getCategories()->willReturn(new ArrayCollection([$category2->reveal()]));

        $sheets = [$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()];
        $resolver = new CategoryNameResolver();
        $result = $resolver->resolveForPreloadSheets($sheets, 'fr');

        $this->assertEquals('Category 2', $result);
    }

    public function testResolveWithTypeWithPosition()
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $sheet1->getType()->willReturn($type1->reveal());
        $sheet2->getType()->willReturn($type1->reveal());
        $sheet3->getType()->willReturn($type2->reveal());
        $category1 = $this->prophesize(Category::class);
        $category2 = $this->prophesize(Category::class);
        $category1->getTitle('fr')->shouldBeCalled()->willReturn('Category 1');
        $category2->getTitle('fr')->shouldNotBeCalled();

        $type1->getId()->willReturn(12);
        $type2->getId()->willReturn(10);
        $type1->getPosition()->willReturn(1);
        $type2->getPosition()->willReturn(2);
        $type1->getCategories()->willReturn(new ArrayCollection([$category1->reveal()]));
        $type2->getCategories()->willReturn(new ArrayCollection([$category2->reveal()]));

        $sheets = [$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()];
        $resolver = new CategoryNameResolver();
        $result = $resolver->resolveForPreloadSheets($sheets, 'fr');

        $this->assertEquals('Category 1', $result);
    }

    public function testResolveWithoutCategory()
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $sheet1->getType()->willReturn($type1->reveal());
        $sheet2->getType()->willReturn($type1->reveal());
        $sheet3->getType()->willReturn($type2->reveal());

        $type1->getId()->willReturn(12);
        $type2->getId()->willReturn(10);
        $type1->getPosition()->willReturn(1);
        $type2->getPosition()->willReturn(2);
        $type1->getCategories()->willReturn(new ArrayCollection());
        $type2->getCategories()->willReturn(new ArrayCollection());

        $sheets = [$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()];
        $resolver = new CategoryNameResolver();
        $result = $resolver->resolveForPreloadSheets($sheets, 'fr');

        $this->assertEquals('', $result);
    }

    public function testResolveWithEmptySheets()
    {
        $sheets = [];

        $resolver = new CategoryNameResolver();
        $result = $resolver->resolveForPreloadSheets($sheets, 'fr');

        $this->assertEquals('', $result);
    }
}
