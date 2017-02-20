<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\CallbackTransformer;

use AppBundle\Form\DataTransformer\EntityToIdTransformer;

class MessageFormType extends AbstractType
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
            ->add('fromUser', 'text')
            ->add('toUser', 'text')
        ;

        $builder->get('fromUser')
            ->addModelTransformer(new EntityToIdTransformer($this->manager->getRepository('UserBundle:User')));

        $builder->get('toUser')
            ->addModelTransformer(new EntityToIdTransformer($this->manager->getRepository('UserBundle:User')));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Message',
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return '';
    }
}