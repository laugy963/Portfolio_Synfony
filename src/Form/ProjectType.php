<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du projet',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('image', UrlType::class, [
                'label' => 'URL de l\'image',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('link', UrlType::class, [
                'label' => 'Lien du projet',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('github', UrlType::class, [
                'label' => 'Lien GitHub',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('technologies', TextType::class, [
                'label' => 'Technologies (séparées par des virgules)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'PHP, Symfony, JavaScript, etc.']
            ])
            ->add('featured', CheckboxType::class, [
                'label' => 'Projet en vedette',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
