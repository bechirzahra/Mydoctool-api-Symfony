<?php

namespace UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\CallbackTransformer;

class OrganizationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'required' => true,
            ))
            ->add('groupName', 'text')
            ->add('groupId', 'text', array(
                'required' => false,
            ))
            ->add('url', 'text')
            // ->add('logo', 'text')
            // ->add('userEmail', 'email', array(
            //     'mapped' => false,
            // ))
            // ->add('userFirstname', 'text', array(
            //     'mapped' => false,
            // ))
            // ->add('userLastname', 'text', array(
            //     'mapped' => false,
            // ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\Organization',
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return '';
    }
}