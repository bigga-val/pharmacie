<?php

namespace App\Form;

//use App\Form\EntityType;
use App\Entity\Produits;
use App\Entity\Approvisionnement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ApprovisionnementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('createdAt')
//            ->add('createdBy')
            ->add('produit', EntityType::class, [
                'class'        => Produits::class,
                'placeholder'  => '-- Sélectionner un produit --',
                'required'     => true,
            ])
            ->add('qty', NumberType::class, [
                'required'    => true,
                'constraints' => [
                    new NotBlank(message: 'La quantité est obligatoire.'),
                    new NotEqualTo(value: 0, message: 'La quantité ne peut pas être zéro.'),
                ],
            ])
            ->add('prixUnitaire', NumberType::class, [
                'mapped'   => false,
                'required' => false,
                'label'    => 'Prix unitaire (Fc)',
            ])
            ->add('cout')

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Approvisionnement::class,
        ]);
    }
}
