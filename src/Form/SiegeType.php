<?php

namespace App\Form;

use App\Entity\Siege;
use App\Entity\Salle;
use App\Enum\TypeSiege;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiegeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => function (Salle $salle) {
                    return 'Salle ' . $salle->getNumeroSalle() . ' - ' . ($salle->getNom() ?? 'Sans nom');
                },
                'label' => 'Salle',
                'placeholder' => 'Choisir une salle',
            ])
            ->add('numero_rangee', TextType::class, [
                'label' => 'Rangée (A, B, C...)',
                'attr' => [
                    'placeholder' => 'A',
                    'maxlength' => 5,
                ],
            ])
            ->add('numero_place', NumberType::class, [
                'label' => 'Numéro de place',
                'attr' => [
                    'placeholder' => '1',
                    'min' => 1,
                ],
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
            ])
            ->add('prix_supplement', NumberType::class, [
                'label' => 'Supplément prix (€)',
                'required' => false,
                'attr' => [
                    'placeholder' => '0.00',
                    'step' => '0.01',
                    'min' => '0',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Siege::class,
        ]);
    }
}   