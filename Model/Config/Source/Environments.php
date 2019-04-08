<?php

namespace Gasperhafner\CustomersProducts\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Environments implements ArrayInterface
{
    const API_PRODUCTION_URL = 'https://artur.com/merlin/api/invitations';
    const API_STAGING_URL = 'https://merlin.kaldi.si/merlin/api/invitations';

    public function toOptionArray()
    {
        return [
            ['value' => self::API_STAGING_URL, 'label' => "sandbox"],
            ['value' => self::API_PRODUCTION_URL, 'label' => "production"]
        ];
    }
}
