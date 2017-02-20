<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\CallbackTransformer;

use AppBundle\Form\DataTransformer\EntityToIdTransformer;
use AppBundle\Form\DataTransformer\EntityToSlugTransformer;

class ItemActivityFormType extends AbstractType
{
    private $manager;

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('answerBool', 'text')
            ->add('answerInt', 'number')
            ->add('answerText', 'text')
            ->add('answerSelect', 'text')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ItemActivity',
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return '';
    }
}