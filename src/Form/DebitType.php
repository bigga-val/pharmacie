<?php

namespace App\Form;

use App\Entity\Debit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant')
            ->add('raison')
            ->add('createdBy')
            ->add('createdAt')
            ->add('DateDebit')
            ->add('taux')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Debit::class,
        ]);
    }
}
