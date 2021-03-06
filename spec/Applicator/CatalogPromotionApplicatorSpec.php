<?php

declare(strict_types=1);

namespace spec\Urbanara\CatalogPromotionPlugin\Applicator;

use Urbanara\CatalogPromotionPlugin\Applicator\CatalogPromotionApplicator;
use Urbanara\CatalogPromotionPlugin\Applicator\CatalogPromotionApplicatorInterface;
use Urbanara\CatalogPromotionPlugin\Model\CatalogAdjustmentInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class CatalogPromotionApplicatorSpec extends ObjectBehavior
{
    function let(FactoryInterface $adjustmentFactory)
    {
        $this->beConstructedWith($adjustmentFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CatalogPromotionApplicator::class);
    }

    function it_is_an_applicator()
    {
        $this->shouldHaveType(CatalogPromotionApplicatorInterface::class);
    }

    function it_applies_catalog_discount_to_order_item(
        AdjustmentInterface $adjustment,
        FactoryInterface $adjustmentFactory,
        OrderItemInterface $orderItem
    ) {
        $orderItem->getUnitPrice()->willReturn(1000);

        $adjustmentFactory->createNew()->willReturn($adjustment);

        $adjustment->setNeutral(true)->shouldBeCalled();
        $adjustment->setType(CatalogAdjustmentInterface::CATALOG_PROMOTION_ADJUSTMENT)->shouldBeCalled();
        $adjustment->setAmount(100)->shouldBeCalled();
        $adjustment->setLabel('Nice promotion')->shouldBeCalled();

        $orderItem->addAdjustment($adjustment)->shouldBeCalled();
        $orderItem->setUnitPrice(900)->shouldBeCalled();

        $this->apply($orderItem, 100, 'Nice promotion');
    }
}
