<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
            ->add('smallDescription', TextType::class, [
                'label' => 'Petite description',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('bannerImage', FileType::class, [
                'label' => 'Image principale (bannière)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('images', FileType::class, [
                'label' => 'Autres images',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('link', UrlType::class, [
                'label' => 'Lien du projet',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('technologies', TextType::class, [
                'label' => 'Technologies (séparées par des virgules)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'PHP, Symfony, JavaScript, etc.']
            ])
            ->add('madeBy', TextType::class, [
                'label' => 'Projet réalisé par',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom ou équipe ayant réalisé le projet']
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
