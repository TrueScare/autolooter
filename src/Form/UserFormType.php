<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => new TranslatableMessage('user.name', domain: 'labels')
            ])
            ->add('email', EmailType::class, [
                'label' => new TranslatableMessage('mail', domain: 'labels')
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => new TranslatableMessage('password.self', domain: 'labels'),
                'mapped' => false,
                'constraints' => [
                    new NotBlank(                        [
                         'message' => new TranslatableMessage('password.blank', domain: 'errors')
                        ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => new TranslatableMessage('password.min_len', domain: 'errors')
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('save', domain: 'labels')
            ])
            ->setAction($options['route']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'route' => ''
        ]);
    }
}
