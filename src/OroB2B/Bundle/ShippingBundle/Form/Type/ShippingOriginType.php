<?php

namespace OroB2B\Bundle\ShippingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\AddressBundle\Form\EventListener\AddressCountryAndRegionSubscriber;

class ShippingOriginType extends AbstractType
{
    const NAME = 'orob2b_shipping_origin';

    /** @var string */
    protected $dataClass;

    /** @var AddressCountryAndRegionSubscriber */
    protected $countryAndRegionSubscriber;

    /**
     * @param AddressCountryAndRegionSubscriber $eventListener
     */
    public function __construct(AddressCountryAndRegionSubscriber $eventListener)
    {
        $this->countryAndRegionSubscriber = $eventListener;
    }

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->countryAndRegionSubscriber);
        $builder
            ->add(
                'country',
                'oro_country',
                [
                    'label' => 'orob2b.shipping.shipping_origin.country.label',
                    'configs' => ['allowClear' => false, 'placeholder' => 'oro.address.form.choose_country']
                ]
            )
            ->add(
                'region',
                'oro_region',
                [
                    'label' => 'orob2b.shipping.shipping_origin.region.label',
                    'configs' => ['allowClear' => false, 'placeholder' => 'oro.address.form.choose_region']
                ]
            )
            ->add(
                'postalCode',
                TextType::class,
                [
                    'label' => 'orob2b.shipping.shipping_origin.postal_code.label',
                    'attr' => ['placeholder' => 'orob2b.shipping.shipping_origin.postal_code.label']
                ]
            )
            ->add(
                'city',
                TextType::class,
                [
                    'label' => 'orob2b.shipping.shipping_origin.city.label',
                    'attr' => ['placeholder' => 'orob2b.shipping.shipping_origin.city.label']
                ]
            )
            ->add(
                'street',
                TextType::class,
                [
                    'label' => 'orob2b.shipping.shipping_origin.street.label',
                    'attr' => ['placeholder' => 'orob2b.shipping.shipping_origin.street.label']
                ]
            )
            ->add(
                'street2',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'orob2b.shipping.shipping_origin.street2.label',
                    'attr' => ['placeholder' => 'orob2b.shipping.shipping_origin.street2.label']
                ]
            )
            ->add(
                'region_text',
                HiddenType::class,
                [
                    'required' => false,
                    'random_id' => true,
                    'label' => 'orob2b.shipping.shipping_origin.region_text.label',
                    'attr' => ['placeholder' => 'orob2b.shipping.shipping_origin.region_text.label']
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'csrf_token_id' => 'shipping_origin'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
