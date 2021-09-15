<?php

namespace Proximum\Vimeet\Tests\Application\Components\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Spot\Recipe;
use Proximum\Vimeet\Application\Components\Spot\ReferenceFactory;

class ReferenceFactoryTest extends TestCase
{
    public static function provideRecipe()
    {
        return [
            [
                new Recipe('A'),
                ['A'],
            ],
            [
                new Recipe('A', 1),
                ['A1'],
            ],
            [
                new Recipe('A', 1, 5),
                ['A1', 'A2', 'A3', 'A4', 'A5'],
            ],
            [
                new Recipe('A', 5, 10),
                ['A05', 'A06', 'A07', 'A08', 'A09', 'A10'],
            ],
            [
                new Recipe('A', 95, 100),
                ['A095', 'A096', 'A097', 'A098', 'A099', 'A100'],
            ],
        ];
    }

    /**
     * @dataProvider provideRecipe
     *
     * @param Recipe $recipe
     * @param array  $references
     */
    public function testCreateFromRecipe(Recipe $recipe, array $references)
    {
        $factory = new ReferenceFactory();

        $this->assertEquals($references, $factory->createFromRecipe($recipe));
    }

    public static function provideRecipes()
    {
        return [
            [
                [new Recipe('A')],
                ['A'],
            ],
            [
                [new Recipe('A', 1)],
                ['A1'],
            ],
            [
                [new Recipe('A', 1, 5)],
                ['A1', 'A2', 'A3', 'A4', 'A5'],
            ],
            [
                [new Recipe('A', 5, 10)],
                ['A05', 'A06', 'A07', 'A08', 'A09', 'A10'],
            ],
            [
                [new Recipe('A'), new Recipe('B', 5, 10)],
                ['A', 'B05', 'B06', 'B07', 'B08', 'B09', 'B10'],
            ],
            [
                [new Recipe('A', 1, 4), new Recipe('B', 5, 10)],
                ['A1', 'A2', 'A3', 'A4', 'B05', 'B06', 'B07', 'B08', 'B09', 'B10'],
            ],
        ];
    }

    /**
     * @dataProvider provideRecipes
     *
     * @param array $recipes
     * @param array $references
     */
    public function testCreateFromRecipes(array $recipes, array $references)
    {
        $factory = new ReferenceFactory();

        $this->assertEquals($references, $factory->createFromRecipes($recipes));
    }
}
