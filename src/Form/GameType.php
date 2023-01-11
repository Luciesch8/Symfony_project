<?php 

namespace App\Form;

use App\Entity\Game;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder 
            ->add('title', null, [
                'label' => 'game.title',
                'help' => 'game.title_help',
            ])
            ->add('editor', null, [
                'label' => 'game.editor',
                'expanded' => true, // Affiche sous forme de radio
                'required' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.name', 'ASC')
                    ;
                },
            ])
            ->add('enabled', ChoiceType::class, [
                'label' => 'game.enabled',
                'choices' => [
                    'no' => false,
                    'yes' => true,
                ],
                'expanded' => true,
            ])
            ->add('description', null, [
                'label' => 'game.description',
                'attr' => [
                    'rows' => 10,
                ]
            ])
            /*->add('releaseYear', ChoiceType::class, [
                'label' => 'game.release_year',
                'choices' => array_combine(range(1972, date('Y')), range(1972, date('Y'))),
            ])*/


            ->add('releaseDate', DateType::class, [
                'label' => 'game.release_date',
                // 'widget' => 'single_text',
                'years' => range(1972, date('Y')),
            ])

            // Insertion du formulaire ImageType dans celui-ci
            ->add('mainImage', ImageType::class, [
                'label' => 'game.main_image',
            ])

            ->add('deleteMainImage', CheckboxType::class, [
                'label' => 'game.delete_main_image',
                'required' => false,
            ])

            ->add('support', options:[
                'label' => 'game.support',
                'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Indique que ce formulaire va traiter les données d'objet de type Game
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}