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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserFormType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'label.username'
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.mail'
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'label.password',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(                        [
                         'message' => $this->translator->trans('error.blank_pw')
                        ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => $this->translator->trans('error.min_len_pw')
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'label.save'
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
