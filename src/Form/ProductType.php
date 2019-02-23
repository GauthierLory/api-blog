<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductTag;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('tag', EntityType::class, array(
                'class' => ProductTag::class,
                'choice_label' => 'name'
            ))
            ->add('description', CKEditorType::class, array(
                'config' => array(
                    'toolbar' => 'standard'
                )
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
