<?php

namespace App\Form;

use App\Entity\Salle;
use App\Entity\Cinema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero_salle', IntegerType::class, [
                'label' => 'Numéro de salle',
            ])
            ->add('nom', TextType::class, [
                'required' => false,
                'label' => 'Nom de la salle (optionnel)',
            ])
            ->add('equipement', TextType::class, [
                'required' => false,
                'label' => 'Équipement spécial',
            ])
            ->add('cinema', EntityType::class, [
                'class' => Cinema::class,
                'choice_label' => 'nom',
                'label' => 'Cinéma',
                'placeholder' => 'Sélectionnez un cinéma',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
        ]);
    }
}
