<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Badge;

use Proximum\Vimeet\Application\Command\Type\Badge\Configure;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $footerPossibilities = Badge::FOOTER_SHOW_POSSIBILITIES;

        if ($options['type']->getCategories()->isEmpty()) {
            unset($footerPossibilities[Badge::FOOTER_SHOW_CATEGORY]);
        }

        $builder
            ->add('header', FileType::class, [
                'required' => false,
                'help' => 'form.badge_configuration.children.header.help',
            ])
            ->add('showHeader', CheckboxType::class, [
                'required' => false,
            ])
            ->add('isMirrored', CheckboxType::class, [
                'required' => false,
            ])
            ->add('leftImage', FileType::class, [
                'required' => false
            ])
            ->add('removeLeftImage', CheckboxType::class, [
                'required' => false,
            ])
            ->add('rightImage', FileType::class, [
                'required' => false
            ])
            ->add('removeRightImage', CheckboxType::class, [
                'required' => false,
            ])
            ->add('isRightImageFullHeight', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showFooterTypeOrCategory', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => $footerPossibilities,
                'choice_label' => function (string $choice, string $key) {
                    return sprintf('form.badge_configuration.children.showFooterTypeOrCategory.choices.%s', $choice);
                },
            ])
            ->add('footerTextColor', TextType::class, [
                'required' => true,
            ])
            ->add('footerColor', TextType::class, [
                'required' => true,
            ])
            ->add('showPosition', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showCountry', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showFirstName', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showLastName', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showSheetTitle', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showQRCode', CheckboxType::class, [
                'required' => false,
            ])
            ->add('activated', CheckboxType::class, [
                'required' => false,
            ])
            ->add('conditioned', CheckboxType::class, [
                'required' => false,
            ])
            ->add('conditionedByPackage', CheckboxType::class, [
                'required' => false,
            ])
            ->add('conditionedByStates', ChoiceType::class, [
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'choices' => Sheet::getAllStates(),
                'choice_label' => function (?string $choice = null, ?int $key = null) {
                    if (null === $key) {
                        return null;
                    }

                    return sprintf('event.sheet.state.%s', $choice);
                },
                'choice_translation_domain' => 'messages',
                'attr' => [
                    'class' => 'select2',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('type')
            ->setAllowedTypes('type', Type::class)
            ->setDefaults([
                'data_class' => Configure::class,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'badge_configuration';
    }
}
