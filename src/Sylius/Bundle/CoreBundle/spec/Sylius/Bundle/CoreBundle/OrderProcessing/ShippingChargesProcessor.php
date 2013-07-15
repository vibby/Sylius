<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Sylius\Bundle\CoreBundle\OrderProcessing;

use PHPSpec2\ObjectBehavior;
use Sylius\Bundle\CoreBundle\Model\Order;

/**
 * Shipping charges processor spec.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
class ShippingChargesProcessor extends ObjectBehavior
{
    /**
     * @param Sylius\Bundle\ResourceBundle\Model\RepositoryInterface      $adjustmentRepository
     * @param Sylius\Bundle\ShippingBundle\Calculator\CalculatorInterface $calculator
     */
    function let($adjustmentRepository, $calculator)
    {
        $this->beConstructedWith($adjustmentRepository, $calculator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Sylius\Bundle\CoreBundle\OrderProcessing\ShippingChargesProcessor');
    }

    function it_implements_Sylius_shipping_charges_processor_interface()
    {
        $this->shouldImplement('Sylius\Bundle\CoreBundle\OrderProcessing\ShippingChargesProcessorInterface');
    }

    /**
     * @param Sylius\Bundle\CoreBundle\Model\OrderInterface $order
     */
    function it_doesnt_apply_any_shipping_charge_if_order_has_no_shipments($order)
    {
        $order->addAdjustment(ANY_ARGUMENT)->shouldNotBeCalled();

        $this->applyShippingCharges($order);
    }

    /**
     * @param Sylius\Bundle\SalesBundle\Model\AdjustmentInterface        $adjustment
     * @param Sylius\Bundle\CoreBundle\Model\OrderInterface              $order
     * @param Sylius\Bundle\ShippingBundle\Model\ShipmentInterface       $shipment
     * @param Sylius\Bundle\ShippingBundle\Model\ShippingMethodInterface $shippingMethod
     */
    function it_applies_calculated_shipping_charge_for_each_shipment_associated_with_the_order(
        $adjustmentRepository, $calculator, $adjustment, $order, $shipment, $shippingMethod
    )
    {
        $adjustmentRepository->createNew()->willReturn($adjustment);
        $order->getShipments()->willReturn(array($shipment));

        $calculator->calculate($shipment)->willReturn(450);

        $shipment->getMethod()->willReturn($shippingMethod);
        $shippingMethod->getName()->willReturn('FedEx');

        $adjustment->setAmount(450)->shouldBeCalled();
        $adjustment->setLabel(Order::SHIPPING_ADJUSTMENT)->shouldBeCalled();
        $adjustment->setDescription('FedEx')->shouldBeCalled();

        $order->addAdjustment($adjustment)->shouldBeCalled();

        $this->applyShippingCharges($order);
    }
}
