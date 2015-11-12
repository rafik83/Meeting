<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Cart\Carts;

use Proximum\Vimeet\Application\Components\Cart\Carts\LibUploadWithChoicesCart;
use Symfony\Component\Config\Definition\Exception\Exception;

class LibUploadWithChoicesCartTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareWithEmptyTemplateAndData()
    {
        $template  = [];
        $dataValue = [];
        $locale    = 'fr';

        $optionCart = new LibUploadWithChoicesCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($cart !== []) {
            throw new Exception('Cart should be empty');
        }
    }

    public function testPrepareWithEmptyTemplate()
    {
        $template  = [];
        $dataValue = [
            "value"    => true,
            "quantity" => 2,
        ];

        $locale    = 'fr';

        $optionCart = new LibUploadWithChoicesCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($cart !== []) {
            throw new Exception('Cart should be empty');
        }
    }

    public function testPrepareWithPlaceholder()
    {
        $template = [
            'label'       =>
                [
                    'fr' => 'Votre logo',
                    'en' => 'Your logo',
                ],
            'description' => [
                'fr' => 'Description en français',
                'en' => 'Description in english',
            ],
            'type'        => 'upload_with_choices',
            'required'    => true,
            'choices'     => [
                '563cb173969ba' => [
                    'label'       => [
                        'fr' => 'L’insertion de votre logo sur le catalogue en ligne',
                        'en' => 'Your logo on the catalog',
                    ],
                    'description' => [
                        'fr' => '',
                        'en' => '',
                    ],
                    'unitPrice'   => 59,
                ],
                '563cb17add8da' =>
                    [
                        'label'       => [
                            'fr' => 'Je ne prends pas',
                            'en' => 'I do not take',
                        ],
                        'description' => [
                            'fr' => '',
                            'en' => '',
                        ],
                        'placeholder' => true,
                        'unitPrice'   => 0,
                    ],
                ],
            ];

        $dataValue = [
            'file'  => null,
            'value' => [
                'value' => '563cb17add8da',
            ],
        ];

        $result = [];

        $locale    = 'fr';

        $optionCart = new LibUploadWithChoicesCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($result !== $cart) {
            throw new Exception('Cart should not be empty');
        }
    }

    public function testPrepare()
    {
        $template = [
            'label'       =>
                [
                    'fr' => 'Votre logo',
                    'en' => 'Your logo',
                ],
            'description' => [
                'fr' => 'Description en français',
                'en' => 'Description in english',
            ],
            'type'        => 'upload_with_choices',
            'required'    => true,
            'choices'     => [
                '563cb173969ba' => [
                    'label'       => [
                        'fr' => 'L’insertion de votre logo sur le catalogue en ligne',
                        'en' => 'Your logo on the catalog',
                    ],
                    'description' => [
                        'fr' => '',
                        'en' => '',
                    ],
                    'unitPrice'   => 59,
                ],
                '563cb17add8da' =>
                    [
                        'label'       => [
                            'fr' => 'Je ne prends pas',
                            'en' => 'I do not take',
                        ],
                        'description' => [
                            'fr' => '',
                            'en' => '',
                        ],
                        'placeholder' => true,
                        'unitPrice'   => 0,
                    ],
            ],
        ];

        $dataValue = [
            'file'  => null,
            'value' => [
                'value' => '563cb173969ba',
            ],
        ];

        $result = [
            'label'     => "Votre logo : L’insertion de votre logo sur le catalogue en ligne",
            'quantity'  => 1,
            'unitPrice' => 59,
            'total'     => 59,
        ];

        $locale = 'fr';

        $optionCart = new LibUploadWithChoicesCart();
        $cart       = $optionCart->prepare($template, $dataValue, $locale);

        if ($result !== $cart) {
            throw new Exception('Cart should not be empty');
        }
    }
}
