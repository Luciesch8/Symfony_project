<?php

namespace App\Form;

use App\Entity\Support;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', options:[
                'label' => 'support.name'
            ])
            ->add('releaseDate', options:[
                'label' => 'support.release_date',
                'widget' => 'single_text'
            ])
            ->add('description', options:[
                'label' => 'support.description',
                'attr' => [ 
                    'rows' => 12
                    ]
            ])
            ->add('constructor', options:[
                'label' => 'support.constructor'

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Support::class,
        ]);
    }
}
