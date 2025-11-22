<?php

namespace App\Form;

use App\Entity\Seance;
use App\Entity\Film;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_seance', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la séance'
            ])
            ->add('heure_debut', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Heure de début'
            ])
            ->add('tmdb_id', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('prix_base', NumberType::class, [
                'label' => 'Prix de base',
                'scale' => 2, // 2 décimales
                'attr'  => [
                    'step' => '0.01',
                    'min'  => '0'
                ]
            ])
            ->add('version', TextType::class, [
                'label' => 'Version (VF/VOST)',
                'attr'  => ['maxlenght' => 10]
            ])
            ->add('salle' , EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'nom',
                'label' => 'Salle'
            ])
            ->add('format' , TextType::class, [
                'label' => 'Format',
                'attr'  => ['maxlenght' => 10]
            ])
            // ->add('film', EntityType::class, [
            //     'class' => Film::class,
            //     'choice_label' => 'nom',
            //     'label' => 'Film'
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Seance::class,
        ]);
    }
}
