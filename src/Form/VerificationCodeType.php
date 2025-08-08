<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class VerificationCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'votre@email.com',
                    'readonly' => $options['readonly_email'] ?? false,
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre adresse email'),
                    new Email(message: 'Veuillez saisir une adresse email valide'),
                ],
            ])
            ->add('code', TextType::class, [
                'label' => 'Code de vérification',
                'attr' => [
                    'class' => 'form-input text-center',
                    'placeholder' => '123456',
                    'maxlength' => 6,
                    'style' => 'font-size: 1.5rem; letter-spacing: 0.3rem; font-family: monospace;',
                    'pattern' => '[0-9]{6}',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir le code de vérification'),
                    new Length(
                        exactly: 6,
                        exactMessage: 'Le code doit contenir exactement 6 chiffres'
                    ),
                    new Regex(
                        pattern: '/^\d{6}$/',
                        message: 'Le code doit contenir uniquement des chiffres'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'readonly_email' => false,
        ]);
    }
}
