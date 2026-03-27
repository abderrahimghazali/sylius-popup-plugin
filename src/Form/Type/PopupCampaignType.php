<?php

declare(strict_types=1);

namespace Abderrahim\SyliusPopupPlugin\Form\Type;

use Abderrahim\SyliusPopupPlugin\Enum\PopupStyle;
use Abderrahim\SyliusPopupPlugin\Enum\ShowFrequency;
use Abderrahim\SyliusPopupPlugin\Enum\TargetAudience;
use Abderrahim\SyliusPopupPlugin\Enum\TargetPages;
use Abderrahim\SyliusPopupPlugin\Enum\TriggerType;
use Abderrahim\SyliusPopupPlugin\Entity\PopupCampaign;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;

final class PopupCampaignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Content tab fields
            ->add('name', TextType::class, [
                'label' => 'popup.form.name',
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                ],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'label' => 'popup.form.title',
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                ],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'popup.form.body',
                'required' => false,
            ])
            ->add('ctaLabel', TextType::class, [
                'label' => 'popup.form.cta_label',
                'required' => false,
            ])
            ->add('ctaUrl', UrlType::class, [
                'label' => 'popup.form.cta_url',
                'required' => false,
            ])
            ->add('discountCode', TextType::class, [
                'label' => 'popup.form.discount_code',
                'required' => false,
                'help' => 'popup.form.discount_code_help',
            ])
            ->add('emailCaptureEnabled', CheckboxType::class, [
                'label' => 'popup.form.email_capture_enabled',
                'required' => false,
            ])

            // Design tab fields
            ->add('style', EnumType::class, [
                'label' => 'popup.form.style',
                'class' => PopupStyle::class,
                'choice_label' => fn (PopupStyle $style) => $style->label(),
            ])
            ->add('backgroundColor', ColorType::class, [
                'label' => 'popup.form.background_color',
            ])
            ->add('textColor', ColorType::class, [
                'label' => 'popup.form.text_color',
            ])
            ->add('buttonColor', ColorType::class, [
                'label' => 'popup.form.button_color',
            ])

            // Targeting tab fields
            ->add('triggerType', EnumType::class, [
                'label' => 'popup.form.trigger_type',
                'class' => TriggerType::class,
                'choice_label' => fn (TriggerType $type) => $type->label(),
            ])
            ->add('triggerDelay', IntegerType::class, [
                'label' => 'popup.form.trigger_delay',
                'help' => 'popup.form.trigger_delay_help',
                'constraints' => [
                    new PositiveOrZero(),
                ],
            ])
            ->add('triggerScrollDepth', IntegerType::class, [
                'label' => 'popup.form.trigger_scroll_depth',
                'help' => 'popup.form.trigger_scroll_depth_help',
                'constraints' => [
                    new Range(min: 0, max: 100),
                ],
            ])
            ->add('showFrequency', EnumType::class, [
                'label' => 'popup.form.show_frequency',
                'class' => ShowFrequency::class,
                'choice_label' => fn (ShowFrequency $freq) => $freq->label(),
            ])
            ->add('targetPages', EnumType::class, [
                'label' => 'popup.form.target_pages',
                'class' => TargetPages::class,
                'choice_label' => fn (TargetPages $pages) => $pages->label(),
            ])
            ->add('targetAudience', EnumType::class, [
                'label' => 'popup.form.target_audience',
                'class' => TargetAudience::class,
                'choice_label' => fn (TargetAudience $audience) => $audience->label(),
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'popup.form.priority',
                'help' => 'popup.form.priority_help',
                'constraints' => [
                    new PositiveOrZero(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PopupCampaign::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'popup_campaign';
    }
}
