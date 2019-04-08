<?php

namespace Artur\CustomersProducts\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ReviewOptions implements ArrayInterface
{
    const REVIEW_WEBSTORE_AND_PRODUCT = 1;
    const REVIEW_WEBSTORE_ONLY = 2;
    const REVIEW_PRODUCT_ONLY = 3;

    public function toOptionArray()
    {
        return [
            ['value' => self::REVIEW_WEBSTORE_AND_PRODUCT, 'label' => "Web store and products"],
            ['value' => self::REVIEW_WEBSTORE_ONLY, 'label' => "Web store only"],
            ['value' => self::REVIEW_PRODUCT_ONLY, 'label' => "Products only"]
        ];
    }

    public static function reviewProfile($reviewOption)
    {
        return $reviewOption == self::REVIEW_WEBSTORE_AND_PRODUCT || $reviewOption == self::REVIEW_WEBSTORE_ONLY;
    }

    public static function reviewProducts($reviewOption)
    {
        return $reviewOption == self::REVIEW_PRODUCT_ONLY;
    }
}
