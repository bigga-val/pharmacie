<?php

namespace App\Form;

use App\Entity\Approvisionnement;
use App\Entity\Produits;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class AjustementStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class'       => Produits::class,
                'placeholder' => '-- Sélectionner un produit --',
                'required'    => true,
            ])
            ->add('qty', NumberType::class, [
                'label'       => 'Quantité (négative = retrait)',
                'required'    => true,
                'constraints' => [
                    new NotBlank(message: 'La quantité est obligatoire.'),
                    new NotEqualTo(value: 0, message: 'La quantité ne peut pas être zéro.'),
                ],
            ])
            ->add('motif', ChoiceType::class, [
                'label'       => 'Motif',
                'required'    => true,
                'constraints' => [
                    new NotBlank(message: 'Le motif est obligatoire.'),
                ],
                'choices'  => [
                    'Correction inventaire' => 'Correction inventaire',
                    'Perte'                 => 'Perte',
                    'Vol'                   => 'Vol',
                    'Produit avarié'        => 'Produit avarié',
                    'Retour client'         => 'Retour client',
                    'Autre'                 => 'Autre',
                ],
                'placeholder' => '-- Sélectionner un motif --',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Approvisionnement::class,
        ]);
    }
}
