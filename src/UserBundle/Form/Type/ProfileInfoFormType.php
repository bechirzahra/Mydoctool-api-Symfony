<?php

namespace UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\Container;

class ProfileInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('maidenname', 'text')
            ->add('email', 'email')
            ->add('phoneNumber', 'text')
            ->add('birthdayDay', 'text')
            ->add('birthdayMonth', 'text')
            ->add('birthdayYear', 'text')
            ->add('weight', 'text')
            ->add('height', 'text')
            ->add('gender', 'text')
            ->add('smoker', 'text')
            ->add('expertise', 'text')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\User',
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return '';
    }
}