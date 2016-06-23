<?php

namespace Omouren\DocumentIdFormBundle\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Omouren\DocumentIdFormBundle\DataTransformer\DocumentToIdTransformer;

/**
 * DocumentIdType class
 *
 * @author Omouren <mouren.olivier@gmail.com>
 */
class DocumentIdType extends AbstractType
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new DocumentToIdTransformer(
                $this->dm,
                $options['class'],
                $options['property'],
                $options['multiple']
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'class',
        ]);
        $resolver->setDefaults([
            'dm'            => null,
            'property'      => null,
            'hidden'        => true,
            'multiple'      => false,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (true === $options['hidden']) {
            $view->vars['type'] = 'hidden';
        }
        if ($options['property'] === null) {
            $view->vars['property'] = $this->dm->getMetadataFactory()->getMetadataFor($options['class'])->getIdentifier()[0];
        } else {
            $view->vars['property'] = $options['property'];
        }
    }

    public function getParent()
    {
        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            return 'text';
        }
        return 'Symfony\Component\Form\Extension\Core\Type\TextType';
    }

    public function getBlockPrefix()
    {
        return 'document_id';
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
