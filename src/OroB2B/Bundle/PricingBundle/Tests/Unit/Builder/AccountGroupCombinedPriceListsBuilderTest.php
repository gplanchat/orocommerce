<?php

namespace OroB2B\Bundle\PricingBundle\Tests\Unit\Builder;

use OroB2B\Bundle\AccountBundle\Entity\AccountGroup;
use OroB2B\Bundle\PricingBundle\Builder\AccountCombinedPriceListsBuilder;
use OroB2B\Bundle\PricingBundle\Builder\AccountGroupCombinedPriceListsBuilder;
use OroB2B\Bundle\PricingBundle\Entity\CombinedPriceList;
use OroB2B\Bundle\PricingBundle\Entity\PriceListAccountGroupFallback;
use OroB2B\Bundle\PricingBundle\Entity\PriceListToAccountGroup;
use OroB2B\Bundle\WebsiteBundle\Entity\Website;

class AccountGroupCombinedPriceListsBuilderTest extends AbstractCombinedPriceListsBuilderTest
{
    /**
     * @var AccountGroupCombinedPriceListsBuilder
     */
    protected $builder;

    /**
     * @var AccountCombinedPriceListsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountBuilder;

    /**
     * @return string
     */
    protected function getPriceListToEntityRepositoryClass()
    {
        return 'OroB2B\Bundle\PricingBundle\Entity\Repository\PriceListToAccountGroupRepository';
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->accountBuilder = $this
            ->getMockBuilder('OroB2B\Bundle\PricingBundle\Builder\AccountCombinedPriceListsBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new AccountGroupCombinedPriceListsBuilder(
            $this->registry,
            $this->priceListCollectionProvider,
            $this->combinedPriceListProvider,
            $this->garbageCollector
        );
        $this->builder->setPriceListToEntityClassName($this->priceListToEntityClass);
        $this->builder->setCombinedPriceListClassName($this->combinedPriceListClass);
        $this->builder->setAccountCombinedPriceListsBuilder($this->accountBuilder);
        $this->builder->setCombinedPriceListToEntityClassName($this->combinedPriceListToEntityClass);
    }

    /**
     * @dataProvider testBuildDataProvider
     * @param boolean $force
     * @param PriceListToAccountGroup $priceListByAccountGroup
     */
    public function testBuildForAll($force, $priceListByAccountGroup)
    {
        $website = new Website();
        $accountGroup = new AccountGroup();
        $this->priceListToEntityRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->willReturn($priceListByAccountGroup);
        $this->priceListToEntityRepository->expects($this->once())
            ->method('getAccountGroupIteratorByFallback')
            ->with($website, PriceListAccountGroupFallback::WEBSITE)
            ->will($this->returnValue([$accountGroup]));
        $this->garbageCollector->expects($this->never())
            ->method($this->anything());

        if (!$priceListByAccountGroup) {
            $this->combinedPriceListToEntityRepository
                ->expects($this->once())
                ->method('delete')
                ->with($accountGroup, $website);
        } else {
            $this->combinedPriceListToEntityRepository
                ->expects($this->never())
                ->method('delete');

            $this->assertRebuild($force, $website, $accountGroup);
        }

        $this->builder->build($website, null, $force);
    }

    /**
     * @return array
     */
    public function testBuildDataProvider()
    {
        return [
            ['force' => true, 'priceListByAccountGroup' => null],
            ['force' => false, 'priceListByAccountGroup' => null],
            ['force' => true, 'priceListByAccountGroup' => new PriceListToAccountGroup()],
            ['force' => false, 'priceListByAccountGroup' => new PriceListToAccountGroup()]
        ];
    }

    /**
     * @dataProvider testBuildDataProvider
     * @param boolean $force
     * @param PriceListToAccountGroup $priceListByAccountGroup
     */
    public function testBuildForAccountGroup($force, $priceListByAccountGroup)
    {
        $website = new Website();
        $accountGroup = new AccountGroup();
        $this->priceListToEntityRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->willReturn($priceListByAccountGroup);
        $this->priceListToEntityRepository->expects($this->never())
            ->method('getAccountGroupIteratorByFallback');
        $this->garbageCollector->expects($this->once())
            ->method('cleanCombinedPriceLists');

        if (!$priceListByAccountGroup) {
            $this->combinedPriceListToEntityRepository
                ->expects($this->once())
                ->method('delete')
                ->with($accountGroup, $website);
        } else {
            $this->combinedPriceListToEntityRepository
                ->expects($this->never())
                ->method('delete');

            $this->assertRebuild($force, $website, $accountGroup);
        }

        $this->builder->build($website, $accountGroup, $force);
    }

    /**
     * @param bool $force
     * @param Website $website
     * @param AccountGroup $accountGroup
     */
    protected function assertRebuild($force, Website $website, AccountGroup $accountGroup)
    {
        $priceListCollection = [$this->getPriceListSequenceMember()];
        $combinedPriceList = new CombinedPriceList();

        $this->priceListCollectionProvider->expects($this->once())
            ->method('getPriceListsByAccountGroup')
            ->with($accountGroup, $website)
            ->willReturn($priceListCollection);

        $this->combinedPriceListProvider->expects($this->once())
            ->method('getCombinedPriceList')
            ->with($priceListCollection, $force)
            ->will($this->returnValue($combinedPriceList));

        $this->combinedPriceListRepository->expects($this->once())
            ->method('updateCombinedPriceListConnection')
            ->with($combinedPriceList, $website, $accountGroup);

        $this->accountBuilder->expects($this->once())
            ->method('buildByAccountGroup')
            ->with($website, $accountGroup, $force);
    }
}
