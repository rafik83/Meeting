<?php

namespace Proximum\Vimeet\Tests\Domain\Template\TemplateObject;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;

class ImageTest extends TestCase
{
    public function testCanDisplayImageBecauseNoProductAttached()
    {
        $image = new Image('myImage', 'image', [], 'fr', 'fr');
        $this->assertTrue($image->canDisplayImage());
    }

    public function testCanDisplayImageBecausePackageIsNotPassable()
    {
        $package = $this->prophesize(Package::class);
        $package->isPassable()->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());

        $image = new Image('myImage', 'image', ['products' => [1]], 'fr', 'fr');
        $image->setSheet($sheet->reveal());
        $this->assertTrue($image->canDisplayImage());
    }

    public function testCanDisplayImageBecausePackageDoesNotHaveAnyProducts()
    {
        $package = $this->prophesize(Package::class);
        $package->isPassable()->willReturn(true);
        $package->hasAtLeastOneProduct([1])->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());

        $image = new Image('myImage', 'image', ['products' => [1]], 'fr', 'fr');
        $image->setSheet($sheet->reveal());
        $this->assertTrue($image->canDisplayImage());

    }

    public function testCanNotDisplayImageBecauseNoSelectedProduct()
    {
        $package = $this->prophesize(Package::class);
        $package->isPassable()->willReturn(true);
        $package->hasAtLeastOneProduct([1])->willReturn(true);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());

        $image = new Image('myImage', 'image', ['products' => [1]], 'fr', 'fr');
        $image->setSheet($sheet->reveal());
        $this->assertFalse($image->canDisplayImage());
    }

    public function testCanDisplayImageBecauseThereIsASelectedProduct()
    {
        $package = $this->prophesize(Package::class);
        $package->isPassable()->willReturn(true);
        $package->hasAtLeastOneProduct([1])->willReturn(true);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());

        $image = new Image('myImage', 'image', ['products' => [1]], 'fr', 'fr');
        $image->setSheet($sheet->reveal());
        $image->setData(['product' => 1]);
        $this->assertTrue($image->canDisplayImage());
    }
}
