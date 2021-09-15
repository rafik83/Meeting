<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Infrastructure\Adapter\ExpressionLanguageAdapter;

class ExpressionLanguageAdapterTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testEvaluate(array $data, bool $expectedResult)
    {
        $expression = "speciality === 'MachineLearning' | speciality in ['IA', 'DeepLearning'] & turnover > 10";

        $expressionLanguageAdapter = new ExpressionLanguageAdapter();
        $this->assertEquals($expectedResult, (bool) $expressionLanguageAdapter->evaluate($expression, $data));
    }

    public function dataProvider()
    {
        return [
            [
                [
                    'speciality' => 'MachineLearning',
                    'turnover' => 1,
                ],
                true,
            ],
            [
                [
                    'speciality' => 'IA',
                    'turnover' => 11,
                ],
                true,
            ],
            [
                [
                    'speciality' => 'IA',
                    'turnover' => 10,
                ],
                false,
            ],
            [
                [
                    'speciality' => 'DeepLearning',
                    'turnover' => 20,
                ],
                true,
            ],
            [
                [
                    'speciality' => 'DeepLearning',
                    'turnover' => 1,
                ],
                false,
            ],
            [
                [
                    'speciality' => 'SpeechRecognition',
                    'turnover' => 20,
                ],
                false,
            ],
        ];
    }
}
