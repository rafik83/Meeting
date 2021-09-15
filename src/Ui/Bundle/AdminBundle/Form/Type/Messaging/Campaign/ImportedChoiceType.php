<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportedChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'placeholder'               => 'admin.sheet.imported.all.label',
            'choice_translation_domain' => 'messages',
            'choices'                   => [
                'event.sheet.imported.imported.label'                    => Constant::IMPORTED,
                'event.sheet.imported.imported_with_connection.label'    => Constant::IMPORTED_WITH_CONNECTION,
                'event.sheet.imported.imported_without_connection.label' => Constant::IMPORTED_WITHOUT_CONNECTION,
                'event.sheet.imported.not_imported.label'                => Constant::NOT_IMPORTED,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'messaging_campaign_imported_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
