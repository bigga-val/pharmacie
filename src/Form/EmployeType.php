<?php

namespace App\Form;

use App\Entity\Employe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomcomplet')
            ->add('matricule')
            ->add('dateembauche', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('categorie')
            ->add('titre')
            ->add('salaireJournalier', NumberType::class, [
                'label' => 'Salaire journalier',
                'required' => false,
                'scale' => 2,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employe::class,
        ]);
    }
}
