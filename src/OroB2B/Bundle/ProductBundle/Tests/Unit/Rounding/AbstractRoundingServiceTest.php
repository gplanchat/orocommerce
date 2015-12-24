<?php

namespace OroB2B\Bundle\ProductBundle\Tests\Unit\Rounding;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use OroB2B\Bundle\ProductBundle\Rounding\AbstractRoundingService;

abstract class AbstractRoundingServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractRoundingService */
    protected $service;

    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = $this->getRoundingService();
    }

    /** AbstractRoundingService */
    abstract protected function getRoundingService();

    /**
     * @dataProvider roundProvider
     *
     * @param string $roundingType
     * @param float|integer $value
     * @param integer $precision
     * @param float|integer $expectedValue
     */
    public function testRound($roundingType, $value, $precision, $expectedValue)
    {
        $this->prepareConfigManager($roundingType, $precision);

        $this->assertEquals($expectedValue, $this->service->round($value, $precision));
    }

    /**
     * @param string $roundingType
     * @param int $precision
     */
    protected function prepareConfigManager($roundingType, $precision)
    {
        $this->configManager->expects($this->any())
            ->method('get')
            ->with($this->isType('string'))
            ->willReturn($roundingType);
    }

    /**
     * @return array
     */
    public function roundProvider()
    {
        return [
            'ceil less then half fraction' => [
                'roundingType' => AbstractRoundingService::ROUND_CEILING,
                'value' => 5.5551,
                'precision' => 3,
                'expectedValue' => 5.556,
            ],
            'ceil more then half fraction' => [
                'roundingType' => AbstractRoundingService::ROUND_CEILING,
                'value' => 5.5559,
                'precision' => 3,
                'expectedValue' => 5.556,
            ],
            'floor less then half fraction' => [
                'roundingType' => AbstractRoundingService::ROUND_FLOOR,
                'value' => 5.5551,
                'precision' => 3,
                'expectedValue' => 5.555,
            ],
            'floor more then half fraction' => [
                'roundingType' => AbstractRoundingService::ROUND_FLOOR,
                'value' => 5.5559,
                'precision' => 3,
                'expectedValue' => 5.555,
            ],
            'up positive value' => [
                'roundingType' => AbstractRoundingService::ROUND_UP,
                'value' => 5.5551,
                'precision' => 3,
                'expectedValue' => 5.556,
            ],
            'up negative value' => [
                'roundingType' => AbstractRoundingService::ROUND_UP,
                'value' => -5.5551,
                'precision' => 3,
                'expectedValue' => -5.556,
            ],
            'down positive value' => [
                'roundingType' => AbstractRoundingService::ROUND_DOWN,
                'value' => 5.5559,
                'precision' => 3,
                'expectedValue' => 5.555,
            ],
            'down negative value' => [
                'roundingType' => AbstractRoundingService::ROUND_DOWN,
                'value' => -5.5559,
                'precision' => 3,
                'expectedValue' => -5.555,
            ],
            'half even up to nearest even' => [
                'roundingType' => AbstractRoundingService::ROUND_HALF_EVEN,
                'value' => 5.5555,
                'precision' => 3,
                'expectedValue' => 5.556,
            ],
            'half even down to nearest even' => [
                'roundingType' => AbstractRoundingService::ROUND_HALF_EVEN,
                'value' => 5.5565,
                'precision' => 3,
                'expectedValue' => 5.556,
            ],
            'half even to nearest' => [
                'roundingType' => AbstractRoundingService::ROUND_HALF_EVEN,
                'value' => 5.5551,
                'precision' => 3,
                'expectedValue' => 5.555,
            ],
            'half_up more then half fraction' => [
                'roundingType' => AbstractRoundingService::ROUND_HALF_UP,
                'value' => 5.5555,
                'precision' => 3,
                'expectedValue' => 5.556,
            ],
            'half_up less then half fraction' => [
                'roundingType' => AbstractRoundingService::ROUND_HALF_UP,
                'value' => 5.5554,
                'precision' => 3,
                'expectedValue' => 5.555,
            ],
            'half_down more then half fraction' => [
                'roundingType' => AbstractRoundingService::ROUND_HALF_DOWN,
                'value' => 5.5555,
                'precision' => 3,
                'expectedValue' => 5.555,
            ],
            'half_down less then half fraction' => [
                'roundingType' => AbstractRoundingService::ROUND_HALF_DOWN,
                'value' => 5.5554,
                'precision' => 3,
                'expectedValue' => 5.555,
            ],
            'round zeros' => [
                'roundingType' => AbstractRoundingService::ROUND_HALF_UP,
                'value' => 5.0000,
                'precision' => 3,
                'expectedValue' => 5.000,
            ],
        ];
    }

    /**
     * @throws \OroB2B\Bundle\ProductBundle\Exception\InvalidRoundingTypeException
     * @param mixed $roundingType
     * @dataProvider invalidRoundingDataProvider
     */
//    public function testInvalidRoundingTypeException($roundingType)
//    {
//        $this->configManager->expects($this->once())
//            ->method('get')
//            ->with($this->isType('string'))
//            ->willReturn($roundingType);
//
//        $this->setExpectedException(
//            '\OroB2B\Bundle\ProductBundle\Exception\InvalidRoundingTypeException',
//            'The type of the rounding is not valid. Allowed the following types: half_up, half_down, ceil, floor.'
//        );
//
//        $this->service->round(1.15, 1);
//    }

    /**
     * @return array
     */
    public function invalidRoundingDataProvider()
    {
        return [
            ['test'],
            [false],
            [null],
        ];
    }
}
