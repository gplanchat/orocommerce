<?php

namespace Oro\Bundle\PromotionBundle\Tests\Unit\Discount;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\PromotionBundle\Discount\AbstractDiscount;
use Oro\Bundle\PromotionBundle\Discount\DiscountInterface;
use Oro\Bundle\PromotionBundle\Discount\Exception\ConfiguredException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class AbstractDiscountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DiscountInterface
     */
    private $discount;

    protected function setUp()
    {
        $this->discount = $this->getMockForAbstractClass(AbstractDiscount::class);
    }

    public function testConfigureDefaultOptions()
    {
        $options = [];

        $this->discount->configure($options);
        $this->assertSame(AbstractDiscount::TYPE_PERCENT, $this->discount->getDiscountType());
        $this->assertSame(0.0, $this->discount->getDiscountValue());
    }

    public function testConfigureFixedAmount()
    {
        $options = [
            AbstractDiscount::DISCOUNT_TYPE => AbstractDiscount::TYPE_AMOUNT,
            AbstractDiscount::DISCOUNT_VALUE => 100.2,
            AbstractDiscount::DISCOUNT_CURRENCY => 'EUR'
        ];

        $this->discount->configure($options);
        $this->assertSame($options[AbstractDiscount::DISCOUNT_TYPE], $this->discount->getDiscountType());
        $this->assertSame($options[AbstractDiscount::DISCOUNT_VALUE], $this->discount->getDiscountValue());
        $this->assertSame($options[AbstractDiscount::DISCOUNT_CURRENCY], $this->discount->getDiscountCurrency());
    }

    public function testConfigurePercent()
    {
        $options = [
            AbstractDiscount::DISCOUNT_TYPE => AbstractDiscount::TYPE_PERCENT,
            AbstractDiscount::DISCOUNT_VALUE => 0.3
        ];

        $this->discount->configure($options);
        $this->assertSame($options[AbstractDiscount::DISCOUNT_TYPE], $this->discount->getDiscountType());
        $this->assertSame($options[AbstractDiscount::DISCOUNT_VALUE], $this->discount->getDiscountValue());
    }

    public function testToStringAmount()
    {
        $options = [
            AbstractDiscount::DISCOUNT_TYPE => AbstractDiscount::TYPE_AMOUNT,
            AbstractDiscount::DISCOUNT_VALUE => 42.5,
            AbstractDiscount::DISCOUNT_CURRENCY => 'USD'
        ];

        $this->discount->configure($options);
        $this->assertEquals('42.5 USD', $this->discount->__toString());
    }

    public function testToStringPercent()
    {
        $options = [
            AbstractDiscount::DISCOUNT_TYPE => AbstractDiscount::TYPE_PERCENT,
            AbstractDiscount::DISCOUNT_VALUE => 0.5
        ];

        $this->discount->configure($options);
        $this->assertEquals('50%', $this->discount->__toString());
    }

    public function testSetMatchingProducts()
    {
        $products = new ArrayCollection([$this->createMock(Product::class)]);
        $this->discount->setMatchingProducts($products);
        $this->assertAttributeSame($products, 'matchingProducts', $this->discount);
    }

    public function testDoubleConfiguration()
    {
        $this->expectException(ConfiguredException::class);
        $this->discount->configure([]);
        $this->discount->configure([]);
    }

    /**
     * @dataProvider invalidOptionsDataProvider
     * @param array $options
     */
    public function testInvalidOptions(array $options)
    {
        $this->expectException(InvalidOptionsException::class);
        $this->discount->configure($options);
    }

    /**
     * @return array
     */
    public function invalidOptionsDataProvider(): array
    {
        return [
            'invalid DISCOUNT_TYPE type' => [
                [
                    AbstractDiscount::DISCOUNT_TYPE => []
                ]
            ],
            'invalid DISCOUNT_VALUE type' => [
                [
                    AbstractDiscount::DISCOUNT_VALUE => 'abc'
                ]
            ],
            'invalid DISCOUNT_CURRENCY typr' => [
                [
                    AbstractDiscount::DISCOUNT_TYPE => AbstractDiscount::TYPE_AMOUNT,
                    AbstractDiscount::DISCOUNT_CURRENCY => 100
                ]
            ],
            'invalid DISCOUNT_CURRENCY code length' => [
                [
                    AbstractDiscount::DISCOUNT_TYPE => AbstractDiscount::TYPE_AMOUNT,
                    AbstractDiscount::DISCOUNT_CURRENCY => 'ABCD'
                ]
            ],
        ];
    }
}
