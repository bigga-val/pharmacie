<?php

namespace App\Form;

use App\Entity\AlerteConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class AlerteConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('actifStockBas', CheckboxType::class, [
                'label'    => 'Activer les alertes stock bas',
                'required' => false,
            ])
            ->add('actifPeremption', CheckboxType::class, [
                'label'    => 'Activer les alertes de péremption',
                'required' => false,
            ])
            ->add('joursAvantPeremption', IntegerType::class, [
                'label'       => 'Alerter X jours avant la péremption',
                'constraints' => [
                    new Range(['min' => 1, 'max' => 365]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AlerteConfig::class,
        ]);
    }
}
