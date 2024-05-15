<?php

namespace App\Form;

use App\Entity\Table;
use App\Struct\RandomItemConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class RandomItemConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('unique_tables', CheckboxType::class, [
                'required' => false,
                'label' => new TranslatableMessage('unique.items', domain: 'labels')
            ])
            ->add('amount', NumberType::class,[
                'required' => false,
                'label' => new TranslatableMessage('amount.items', domain: 'labels')
            ])
            ->add('tables', ChoiceType::class, [
                'required' => false,
                'choices' => $options['tableChoices'],
                'choice_label' => 'name',
                'choice_value' => function(?Table $table): int{
                  return $table ? $table->getId(): '';
                },
                'multiple' => true,
                'label' => new TranslatableMessage('choice.tables', domain: 'labels')
            ])
            ->add('submit', SubmitType::class, [
                'label'=> new TranslatableMessage('item.random', domain: 'labels')
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RandomItemConfig::class,
            'tableChoices' => Table::class,
        ]);
    }
}