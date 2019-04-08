<?php
/**
 * A Magento 2 module named Gasperhafner/CustomersProducts
 * Copyright (C) 2019
 *
 * This file is part of Gasperhafner/CustomersProducts.
 *
 * Gasperhafner/CustomersProducts is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Gasperhafner\CustomersProducts\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const API_USERNAME = "artur/config/username";
    const API_PASSWORD = "artur/config/password";
    const API_ENVIRONMENT = "artur/config/environment";

    private $curl;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->curl = $curl;
        parent::__construct($context);
    }

    public function sendDataOnApi($params)
    {
        $username = $this->scopeConfig->getValue(
            self::API_USERNAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $password = $this->scopeConfig->getValue(
            self::API_PASSWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $apiUrl = $this->scopeConfig->getValue(
            self::API_ENVIRONMENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->curl->setCredentials($username, $password);
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->post($apiUrl, $params);
    }
}
