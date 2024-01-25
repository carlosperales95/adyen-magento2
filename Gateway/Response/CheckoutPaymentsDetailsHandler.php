<?php
/**
 *
 * Adyen Payment module (https://www.adyen.com/)
 *
 * Copyright (c) 2022 Adyen BV (https://www.adyen.com/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>
 */

namespace Adyen\Payment\Gateway\Response;

use Adyen\Payment\Helper\Data;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CheckoutPaymentsDetailsHandler implements HandlerInterface
{
    /** @var Data  */
    protected $adyenHelper;
    const ADYEN_BOLETO = 'adyen_boleto';

    public function __construct(
        Data $adyenHelper
    ) {
        $this->adyenHelper = $adyenHelper;
    }

    /**
     * This is being used for all checkout methods (adyen hpp payment method)
     *
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();

        // set transaction not to processing by default wait for notification
        $payment->setIsTransactionPending(true);

        // Email sending is set at CheckoutDataBuilder for Boleto
        // Otherwise, we don't want to send a confirmation email
        if ($payment->getMethod() != self::ADYEN_BOLETO) {
            $payment->getOrder()->setCanSendNewEmailFlag(false);
        }

        if (!empty($response['pspReference'])) {
            // set pspReference as transactionId
            $payment->setCcTransId($response['pspReference']);
            $payment->setLastTransId($response['pspReference']);

            // set transaction
            $payment->setTransactionId($response['pspReference']);
        }

        // do not close transaction so you can do a cancel() and void
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
    }
}
