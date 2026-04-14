<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectType extends AbstractType
{
    private const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Project|null $project */
        $project = $builder->getData();
        $existingImages = $project?->getImages() ?? [];

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du projet',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le titre est obligatoire.'),
                    new Assert\Length(min: 3, max: 255),
                ],
            ])
            ->add('smallDescription', TextType::class, [
                'label' => 'Petite description',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\Length(max: 255),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 6],
                'constraints' => [
                    new Assert\NotBlank(message: 'La description detaillee est obligatoire.'),
                    new Assert\Length(min: 10),
                ],
            ])
            ->add('bannerImageFile', FileType::class, [
                'label' => 'Image principale (bannière)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => '.jpg,.jpeg,.png,.gif,.webp',
                    'data-project-banner-input' => 'true',
                ],
                'help' => 'Formats acceptes : JPG, PNG, GIF, WEBP. Taille maximale : 2 Mo.',
                'constraints' => [
                    new Assert\File(
                        maxSize: '2M',
                        mimeTypes: self::ALLOWED_IMAGE_MIME_TYPES,
                        maxSizeMessage: 'La banniere ne doit pas depasser 2 Mo.',
                        mimeTypesMessage: 'La banniere doit etre une image JPG, PNG, GIF ou WEBP.'
                    ),
                ],
            ])
            ->add('removeBannerImage', CheckboxType::class, [
                'label' => 'Supprimer la banniere actuelle',
                'mapped' => false,
                'required' => false,
            ])
            ->add('galleryImages', FileType::class, [
                'label' => 'Autres images',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => '.jpg,.jpeg,.png,.gif,.webp',
                    'data-project-gallery-input' => 'true',
                ],
                'help' => 'Ajoutez une ou plusieurs images supplementaires. Taille maximale : 2 Mo par image.',
                'constraints' => [
                    new Assert\All([
                        new Assert\File(
                            maxSize: '2M',
                            mimeTypes: self::ALLOWED_IMAGE_MIME_TYPES,
                            maxSizeMessage: 'Chaque image de galerie doit rester sous 2 Mo.',
                            mimeTypesMessage: 'Les images de galerie doivent etre au format JPG, PNG, GIF ou WEBP.'
                        ),
                    ]),
                ],
            ])
            ->add('removeImages', ChoiceType::class, [
                'label' => 'Images existantes a supprimer',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => array_combine($existingImages, $existingImages) ?: [],
            ])
            ->add('link', UrlType::class, [
                'label' => 'Lien du projet',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\Length(max: 255),
                ],
            ])
            ->add('technologies', TextType::class, [
                'label' => 'Technologies (séparées par des virgules)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'PHP, Symfony, JavaScript, etc.'],
                'constraints' => [
                    new Assert\Length(max: 2000),
                ],
            ])
            ->add('madeBy', TextType::class, [
                'label' => 'Projet réalisé par',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom ou équipe ayant réalisé le projet'],
                'constraints' => [
                    new Assert\Length(max: 255),
                ],
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
