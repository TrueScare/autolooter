<?php

namespace App\Form;

use App\Entity\Table;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveTablesBetweenTablesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parent', EntityType::class, [
                'class' => Table::class,
                'choice_label' => 'name',
                'choices' => $options['choices'],
                'label' => 'label.parent'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'label.save',
            ])
            ->setAction($options['route']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => Table::class,
            'route' => ''
        ]);
    }
}