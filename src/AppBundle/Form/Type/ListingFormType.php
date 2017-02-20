<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\CallbackTransformer;

use AppBundle\Form\DataTransformer\EntityToSlugTransformer;
use AppBundle\Form\DataTransformer\EntitiesToSlugsTransformer;

class ListingFormType extends AbstractType
{
    private $manager;

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('text', 'text')
            ->add('color', 'text')
            ->add('durationValue')
            ->add('durationUnit')
            ->add('category', 'text', array(
                'invalid_message' => 'That is not a valid category slug',
            ))
        ;

        $builder->get('category')
            ->addModelTransformer(new EntityToSlugTransformer($this->manager->getRepository('AppBundle:Category')))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Listing',
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return '';
    }
}