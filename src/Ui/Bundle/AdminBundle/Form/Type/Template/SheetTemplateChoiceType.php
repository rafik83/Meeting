<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template;

use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SheetTemplateChoiceType extends AbstractType
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @param SheetTemplateRepositoryInterface $templateRepository
     */
    public function __construct(SheetTemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('event')
            ->setDefaults([
            'choices'          => function (Options $options) {
                return $this->getResults($options);
            },
            'choice_label'     => 'title',
            'repositoryMethod' => function (SheetTemplateRepositoryInterface $templateRepository) {
                return $templateRepository->getBaseTemplates();
            },
            'repositoryMethodEvent' => function (Options $options) {
                return function (SheetTemplateRepositoryInterface $templateRepository) use ($options) {
                    return $templateRepository->getTemplateForGivenEvent($options['event']);
                };
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
     * @param Options $options
     *
     * @return array
     */
    private function getResults(Options $options): array
    {
        $baseTemplates = $options['repositoryMethod']($this->templateRepository);
        $eventTemplates = $options['repositoryMethodEvent']($this->templateRepository);

        $templates = [];

        if (!empty($baseTemplates)) {
            $templates['form.type_template.sheet.base'] = $baseTemplates;
        }

        if (!empty($eventTemplates)) {
            $templates['form.type_template.sheet.event'] = $eventTemplates;
        }

        return $templates;
    }
}
