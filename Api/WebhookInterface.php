<?php
/**
* Copyright © Magento, Inc. All rights reserved.
*/
namespace Globalpay\PaymentGateway\Api;
/**
* @api
*/
interface WebhookInterface
{
  /**
   * Update order via payment gateway webhook
   * @return \Magento\Framework\Webapi\Exception
   */
  public function updateOrderWebhook();
}
