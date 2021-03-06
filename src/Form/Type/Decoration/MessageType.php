<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\Form\Type\Decoration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Urbanara\CatalogPromotionPlugin\Form\Type\TranslationsType;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class MessageType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TranslationsType::class, [
                'entry_type' => TextType::class,
                'label' => 'urbanara_catalog_promotion.form.catalog_promotion_decoration.message.message',
            ])
            ->add('activeOnProductDisplayPage', CheckboxType::class, [
                'label' => 'urbanara_catalog_promotion.form.catalog_promotion_decoration.active_on_product_display_page',
                'required' => false,
            ])
            ->add('activeOnProductListingPage', CheckboxType::class, [
                'label' => 'urbanara_catalog_promotion.form.catalog_promotion_decoration.active_on_product_listing_page',
                'required' => false,
            ])
            ->add('activeOnCheckoutPage', CheckboxType::class, [
                'label' => 'urbanara_catalog_promotion.form.catalog_promotion_decoration.active_on_checkout_page',
                'required' => false,
            ])
        ;
    }
}
