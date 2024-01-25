<?php

namespace Adyen\Payment\Test\Unit\Helper;

use Adyen\Payment\Helper\Webhook\AuthorisationWebhookHandler;
use Adyen\Payment\Helper\Webhook\CancellationWebhookHandler;
use Adyen\Payment\Helper\Webhook\CancelOrRefundWebhookHandler;
use Adyen\Payment\Helper\Webhook\CaptureWebhookHandler;
use Adyen\Payment\Helper\Webhook\ManualReviewAcceptWebhookHandler;
use Adyen\Payment\Helper\Webhook\ManualReviewRejectWebhookHandler;
use Adyen\Payment\Helper\Webhook\OfferClosedWebhookHandler;
use Adyen\Payment\Helper\Webhook\OrderClosedWebhookHandler;
use Adyen\Payment\Helper\Webhook\OrderOpenedWebhookHandler;
use Adyen\Payment\Helper\Webhook\PendingWebhookHandler;
use Adyen\Payment\Helper\Webhook\RecurringContractWebhookHandler;
use Adyen\Payment\Helper\Webhook\RefundFailedWebhookHandler;
use Adyen\Payment\Helper\Webhook\RefundWebhookHandler;
use Adyen\Payment\Helper\Webhook\WebhookHandlerFactory;
use Adyen\Payment\Logger\AdyenLogger;
use Adyen\Payment\Model\Notification;
use Adyen\Payment\Model\Order\Payment as AdyenPaymentModel;
use Adyen\Payment\Test\Unit\AbstractAdyenTestCase;
use Magento\Sales\Model\Order;


class WebhookHandlerFactoryTest extends AbstractAdyenTestCase
{
    public function getNotificationsHandlersMap()
    {
        return [
          [Notification::HANDLED_EXTERNALLY, AuthorisationWebhookHandler::class],
            [Notification::AUTHORISATION, AuthorisationWebhookHandler::class],
            [Notification::CAPTURE, CaptureWebhookHandler::class],
            [Notification::OFFER_CLOSED, OfferClosedWebhookHandler::class],
            [Notification::REFUND, RefundWebhookHandler::class ],
            [Notification::REFUND_FAILED, RefundFailedWebhookHandler::class],
            [Notification::MANUAL_REVIEW_ACCEPT, ManualReviewAcceptWebhookHandler::class],
            [Notification::MANUAL_REVIEW_REJECT, ManualReviewRejectWebhookHandler::class],
            [Notification::RECURRING_CONTRACT, RecurringContractWebhookHandler::class],
            [Notification::PENDING, pendingWebhookHandler::class],
            [Notification::CANCELLATION, CancellationWebhookHandler::class],
            [Notification::CANCEL_OR_REFUND, CancelOrRefundWebhookHandler::class],
            [Notification::ORDER_CLOSED, OrderClosedWebhookHandler::class]
        ];
    }

    /**
     * @dataProvider getNotificationsHandlersMap
     */
    public function testCreateHandler(string $notificationType, string $handlerType): void
    {
        $adyenLogger = $this->createMock(AdyenLogger::class);
        $authorisationWebhookHandler = $this->createMock(AuthorisationWebhookHandler::class);
        $captureWebhookHandler = $this->createMock(CaptureWebhookHandler::class);
        $offerClosedWebhookHandler = $this->createMock(OfferClosedWebhookHandler::class);
        $refundWebhookHandler = $this->createMock(RefundWebhookHandler::class);
        $refundFailedWebhookHandler = $this->createMock(RefundFailedWebhookHandler::class);
        $manualReviewAcceptWebhookHandler = $this->createMock(ManualReviewAcceptWebhookHandler::class);
        $manualReviewRejectWebhookHandler = $this->createMock(ManualReviewRejectWebhookHandler::class);
        $recurringContractWebhookHandler = $this->createMock(RecurringContractWebhookHandler::class);
        $pendingWebhookHandler = $this->createMock(PendingWebhookHandler::class);
        $cancellationWebhookHandler = $this->createMock(CancellationWebhookHandler::class);
        $cancelOrRefundWebhookHandler = $this->createMock(CancelOrRefundWebhookHandler::class);
        $orderClosedWebhookHandler = $this->createMock(OrderClosedWebhookHandler::class);
        $orderOpenedWebhookHandler = $this->createMock(OrderOpenedWebhookHandler::class);

        $factory = new WebhookHandlerFactory(
            $adyenLogger,
            $authorisationWebhookHandler,
            $captureWebhookHandler,
            $offerClosedWebhookHandler,
            $refundWebhookHandler,
            $refundFailedWebhookHandler,
            $manualReviewAcceptWebhookHandler,
            $manualReviewRejectWebhookHandler,
            $recurringContractWebhookHandler,
            $pendingWebhookHandler,
            $cancellationWebhookHandler,
            $cancelOrRefundWebhookHandler,
            $orderClosedWebhookHandler,
            $orderOpenedWebhookHandler
        );

        $handler = $factory->create($notificationType);
        $this->assertInstanceOf($handlerType, $handler);
    }
}
