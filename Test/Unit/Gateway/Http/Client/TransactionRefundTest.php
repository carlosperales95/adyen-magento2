<?php
/**
 *
 * Adyen Payment module (https://www.adyen.com/)
 *
 * Copyright (c) 2024 Adyen N.V. (https://www.adyen.com/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>
 */

namespace Adyen\Payment\Test\Unit\Gateway\Http\Client;

use Adyen\Client;
use Adyen\Payment\Gateway\Http\Client\TransactionRefund;
use Adyen\Payment\Helper\Data;
use Adyen\Payment\Helper\Idempotency;
use Adyen\Payment\Test\Unit\AbstractAdyenTestCase;
use Adyen\Service\Checkout;
use Magento\Payment\Gateway\Http\TransferInterface;
use PHPUnit\Framework\MockObject\MockObject;

class TransactionRefundTest extends AbstractAdyenTestCase
{
    /**
     * @var Data|MockObject
     */
    private $adyenHelperMock;

    /**
     * @var Idempotency|MockObject
     */
    private $idempotencyHelperMock;

    /**
     * @var TransactionRefund
     */
    private $transactionRefund;

    protected function setUp(): void
    {
        $this->adyenHelperMock = $this->createMock(Data::class);
        $this->idempotencyHelperMock = $this->createMock(Idempotency::class);

        $this->transactionRefund = new TransactionRefund(
            $this->adyenHelperMock,
            $this->idempotencyHelperMock
        );
    }

    public function testPlaceRequestIncludesHeadersInRequest()
    {
        $requestBody = [
            'amount' => ['value' => 1000, 'currency' => 'EUR'],
            'paymentPspReference' => '123456789'
        ];

        $headers = ['idempotencyExtraData' => ['order_id' => '1001']];

        $transferObjectMock = $this->createConfiguredMock(TransferInterface::class, [
            'getBody' => [$requestBody],
            'getHeaders' => $headers,
            'getClientConfig' => []
        ]);

        $checkoutServiceMock = $this->createMock(Checkout::class);
        $adyenClientMock = $this->createMock(Client::class);

        $this->adyenHelperMock->method('initializeAdyenClientWithClientConfig')->willReturn($adyenClientMock);
        $this->adyenHelperMock->method('createAdyenCheckoutService')->willReturn($checkoutServiceMock);
        $this->adyenHelperMock->method('buildRequestHeaders')->willReturn(['custom-header' => 'value']);

        $this->idempotencyHelperMock->expects($this->once())
            ->method('generateIdempotencyKey')
            ->with($requestBody, $headers['idempotencyExtraData'])
            ->willReturn('generated_idempotency_key');

        $checkoutServiceMock->expects($this->once())
            ->method('refunds')
            ->with(
                $this->equalTo($requestBody),
                $this->callback(function ($requestOptions) {
                    $this->assertArrayHasKey('idempotencyKey', $requestOptions);
                    $this->assertArrayHasKey('headers', $requestOptions);
                    $this->assertEquals('generated_idempotency_key', $requestOptions['idempotencyKey']);
                    $this->assertArrayHasKey('custom-header', $requestOptions['headers']);
                    return true;
                })
            )
            ->willReturn(['pspReference' => 'refund_psp_reference']);

        $responses = $this->transactionRefund->placeRequest($transferObjectMock);

        $this->assertIsArray($responses);
        $this->assertCount(1, $responses);
        $this->assertArrayHasKey('pspReference', $responses[0]);
    }
}
