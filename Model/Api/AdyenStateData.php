<?php
/**
 *
 * Adyen Payment Module
 *
 * Copyright (c) 2023 Adyen N.V.
 * This file is open source and available under the MIT license.
 * See the LICENSE file for more info.
 *
 * Author: Adyen <magento@adyen.com>
 */

namespace Adyen\Payment\Model\Api;

use Adyen\Payment\Api\AdyenStateDataInterface;
use Adyen\Payment\Helper\StateData as StateDataHelper;

class AdyenStateData implements AdyenStateDataInterface
{
    private StateDataHelper $stateDataHelper;

    public function __construct(
        StateDataHelper $stateDataHelper
    ) {
        $this->stateDataHelper = $stateDataHelper;
    }

    public function save(string $stateData, int $quoteId): void
    {
        $this->stateDataHelper->saveStateData($stateData, $quoteId);
    }

    public function remove(int $stateDataId, int $quoteId): bool
    {
        return $this->stateDataHelper->removeStateData($stateDataId, $quoteId);
    }
}
