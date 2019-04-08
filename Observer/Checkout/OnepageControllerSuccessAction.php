<?php
/**
 * A Magento 2 module named Artur/CustomersProducts
 * Copyright (C) 2019
 *
 * This file is part of Artur/CustomersProducts.
 *
 * Artur/CustomersProducts is free software: you can redistribute it and/or modify
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

namespace Artur\CustomersProducts\Observer\Checkout;

use Artur\CustomersProducts\Model\Config\Source\ReviewOptions;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Artur\CustomersProducts\Helper\Data as ArturApi;

class OnepageControllerSuccessAction implements \Magento\Framework\Event\ObserverInterface
{
    const ENABLED_CONFIG = "artur/config/enabled";
    const COMPANY_ID_CONFIG = "artur/config/company_id";
    const REVIEW_OPTION = "artur/config/review_option";
    const SEND_REVIEW_AFTER_HOURS_CONFIG = "artur/config/send_product_review_after_hours";
    const SEND_AFTER_HOURS = "artur/config/send_after_hours";
    const TEST_EMAIL = "artur/config/test_email_address";

    private $arturEnabled = false;
    private $testEmail;
    private $companyId;
    private $reviewProfile;
    private $reviewProducts;
    private $sendReviewAfterHours;
    private $sendAfterHours;
    private $orderRepository;
    private $productRepository;
    private $categoryRepository;
    private $productFactory;
    private $imageHelper;
    private $arturApi;
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductFactory $productFactory
     * @param Image $imageHelper
     * @param ArturApi $arturApi
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        ProductFactory $productFactory,
        Image $imageHelper,
        ArturApi $arturApi
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->arturApi = $arturApi;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $this->prepareConfig();

        $products = [];

        if ($this->arturEnabled && extension_loaded("curl")) {
            $orderIds = $observer->getEvent()->getOrderIds();

            if (!is_array($orderIds) || (!array_key_exists(0, $orderIds))) {
                return;
            }

            $order = $this->orderRepository->get($orderIds[0]);

            if (!$order->getId()) {
                return;
            }

            $payload = [
                'test' => !empty($this->testEmail),
                'customerEmail' => empty($this->testEmail) ? $order->getCustomerEmail() : $this->testEmail,
                'purchaseDateUtcMs' => strtotime($order->getCreatedAt()),
                'customerName' => $order->getCustomerFirstname(),
                'customerSurname' => $order->getCustomerLastname(),
                'profileId' => $this->companyId,
                'reviewProfile' => $this->reviewProfile,
                'sendAfterHours' => $this->sendAfterHours,
                'sendProductReviewAfterHours' => $this->sendReviewAfterHours,
                'orderNumber' => $order->getIncrementId(),
            ];

            foreach ($order->getItems() as $item) {
                $product = $this->productRepository->getById($item->getProductId());

                if (!$product->isVisibleInSiteVisibility()) {
                    continue;
                }

                $tags = [];

                $_product = $this->productFactory->create()->loadByAttribute("sku", $item->getSku());

                $categoriesIds = $_product->getCategoryIds();

                foreach ($categoriesIds as $categoryId) {
                    $category = $this->categoryRepository->get($categoryId);
                    $tags[] = $category->getName();
                }

                $products[] = [
                    'sourceId' => $item->getSku(),
                    "imgUrl" => $this->imageHelper->init($_product, "category_page_list")->getUrl(),
                    'group' => "produkti",
                    'name' => $item->getName(),
                    'description' => base64_encode($_product->getData("description")),
                    'tags' => array_unique($tags)
                ];
            }

            $payload['products'] = $products;
            $this->arturApi->sendDataOnApi(json_encode($payload));
        }
    }

    private function prepareConfig()
    {
        $this->arturEnabled = $this->scopeConfig->getValue(
            self::ENABLED_CONFIG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->companyId = $this->scopeConfig->getValue(
            self::COMPANY_ID_CONFIG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $reviewOption = $this->scopeConfig->getValue(
            self::REVIEW_OPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->reviewProfile = ReviewOptions::reviewProfile($reviewOption);
        $this->reviewProducts = ReviewOptions::reviewProducts($reviewOption);

        $this->testEmail = $this->scopeConfig->getValue(
            self::TEST_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->sendReviewAfterHours = $this->scopeConfig->getValue(
            self::SEND_REVIEW_AFTER_HOURS_CONFIG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->sendAfterHours = $this->scopeConfig->getValue(
            self::SEND_AFTER_HOURS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
