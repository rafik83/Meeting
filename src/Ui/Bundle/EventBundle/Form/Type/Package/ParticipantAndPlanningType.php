<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantAndPlanningType extends AbstractType
{
    /** @var QuantityMaxGuesser */
    private $quantityMaxGuesser;

    public function __construct(QuantityMaxGuesser $quantityMaxGuesser)
    {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];
        $locale = $options['locale'];
        $package = $sheet->getPackage();
        $participantProducts = $package->getParticipants();

        foreach ($sheet->getParticipantsArray() as $participant) {
            $builder->add($participant->getId(), ChoiceType::class, [
                'choices' => $participantProducts,
                'choice_value' => static function (Product $product = null) {
                    return null !== $product ? $product->getId() : null;
                },
                'choice_label' => static function (Product $product = null) use ($locale) {
                    return null !== $product ? $product->getTitle($locale) : null;
                },
                'placeholder' => 'form.participantAndPlanning.participantProduct.selectAnOption',
                'required' => true,
            ]);
        }

        $maxErrorMessage = 'package.planning.quantityMax';

        if ($package->canPlanningBeBought()) {
            $builder->add('planningQuantity', QuantityAndParticipantsType::class, [
                'label' => false,
                'max' => $this->quantityMaxGuesser->getMaxPlanning($sheet),
                'minMessage' => 'package.planning.quantityMin',
                'maxMessage' => $maxErrorMessage,
                'sheet' => $options['sheet'],
                'locale' => $options['locale'],
                'isAttributable' => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setRequired(['sheet', 'locale']);
        $optionsResolver->addAllowedTypes('sheet', Sheet::class);
        $optionsResolver->addAllowedTypes('locale', 'string');
        $optionsResolver->setDefaults(
            [
                'data_class' => SelectParticipantAndPlanning::class,
            ]
        );
    }
}
