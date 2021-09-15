<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package;

use DateTime;
use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionsType extends AbstractType
{
    /**
     * @var QuantityMaxGuesser
     */
    private $quantityMaxGuesser;

    /**
     * @param QuantityMaxGuesser $quantityMaxGuesser
     */
    public function __construct(QuantityMaxGuesser $quantityMaxGuesser)
    {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];

        $products = $sheet->getPackage()->getAvailablesOptions($options['now']);

        foreach ($products as $product) {
            $builder->add(
                $product->getId(),
                QuantityAndParticipantsType::class,
                [
                    'label' => false,
                    'max' => $this->quantityMaxGuesser->getMaxByProduct($sheet, $product),
                    'minMessage' => 'package.product.quantityMin',
                    'maxMessage' => 'package.product.quantityMax',
                    'sheet' => $options['sheet'],
                    'locale' => $options['locale'],
                    'isAttributable' => $product->isAttributable(),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['sheet', 'locale']);
        $optionsResolver->addAllowedTypes('sheet', Sheet::class);
        $optionsResolver->setDefaults(
            [
                'data_class' => SelectOptions::class,
                'now'        => new DateTime(),
            ]
        );
    }
}
