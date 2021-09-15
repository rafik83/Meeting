<?php


namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTemplateChoiceType extends AbstractType
{
    /** @var FormTemplateRepositoryInterface */
    private $templateRepository;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        FormTemplateRepositoryInterface $templateRepository,
        TranslatorInterface $translator
    ) {
        $this->templateRepository = $templateRepository;
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('event')
            ->setDefaults([
                'choices' => function (Options $options) {
                    return $this->getResults($options);
                },
                'choice_label' => function (FormTemplate $template) {
                    return sprintf(
                        '%s (%s)',
                        $template->getTitle(),
                        $this->translator->trans(
                            $template->isPublished()
                                ? 'form.type_create.children.formTemplates.status.published'
                                : 'form.type_create.children.formTemplates.status.draft'
                            ,
                            [],
                            'forms'
                        )
                    );
                }
            ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    private function getResults(Options $options): array
    {
        return $this->templateRepository->findByEvent($options['event']);
    }
}
