<?php

namespace App\Form;


use App\Entity\CategorieProduit;
use App\Entity\Produits;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;


class ProduitsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code')
            ->add('designation')
            ->add('Categorie', EntityType::class, [
                'class' => CategorieProduit::class,
                'choice_label' => 'designation',
            ])
            ->add('prixAchat')
            ->add('prix')
            ->add('maximum')
            ->add('minimum')
            ->add('uniteMesure')
            //->add('fabricant')
            ->add('preemption', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('imageFile', FileType::class, [
                'label'    => 'Image du produit',
                'mapped'   => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'         => '2M',
                        'mimeTypes'       => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage'=> 'Formats acceptés : JPG, PNG, WEBP (max 2 Mo)',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produits::class,
        ]);
    }
}
