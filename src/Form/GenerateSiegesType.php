<?php

namespace App\Form;

use App\Entity\Salle;
use App\Enum\TypeSiege;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateSiegesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => function (Salle $salle) {
                    $capacite = $salle->getCapacite();
                    $nom = $salle->getNom() ?? 'Sans nom';
                    return "Salle {$salle->getNumeroSalle()} - {$nom} ({$capacite} sièges)";
                },
                'label' => 'Salle',
                'placeholder' => 'Choisir une salle',
            ])
            ->add('nb_rangees', IntegerType::class, [
                'label' => 'Nombre de rangées',
                'attr' => [
                    'placeholder' => '10',
                    'min' => 1,
                    'max' => 26, // A-Z
                ],
                'data' => 10,
            ])
            ->add('nb_places_par_rangee', IntegerType::class, [
                'label' => 'Places par rangée',
                'attr' => [
                    'placeholder' => '12',
                    'min' => 1,
                    'max' => 50,
                ],
                'data' => 12,
            ])
            ->add('type', EnumType::class, [
                'class' => TypeSiege::class,
                'label' => 'Type de siège',
                'choice_label' => function (TypeSiege $type) {
                    return match ($type) {
                        TypeSiege::NORMAL => '🪑 Standard',
                        TypeSiege::VIP => '⭐ VIP',
                        TypeSiege::HANDICAPE => '♿ PMR / Handicapé',
                        TypeSiege::OCCUPE => '🚫 Occupé / Indisponible',
                    };
                },
                'data' => TypeSiege::NORMAL,
            ])
            ->add('prix_supplement', NumberType::class, [
                'label' => 'Supplément prix (€)',
                'required' => false,
                'attr' => [
                    'placeholder' => '0.00',
                    'step' => '0.01',
                    'min' => '0',
                ],
                'data' => '0.00',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}