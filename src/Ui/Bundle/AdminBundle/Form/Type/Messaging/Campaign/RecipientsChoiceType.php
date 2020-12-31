<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Choices for recipients in a messaging campaign: sheet owner, partipants and/or billing contact
 */
class RecipientsChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices'                   => Campaign::getRecipientChoices(),
            'choice_translation_domain' => 'messages',
            'choice_label'              => function ($currentChoice) {
                return sprintf('admin.messaging_campaign.recipients.%s', $currentChoice);
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'messaging_campaign_recipients';
    }
}
