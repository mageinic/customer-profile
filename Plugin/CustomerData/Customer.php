<?php
/**
 * MageINIC
 * Copyright (C) 2023 MageINIC <support@mageinic.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://opensource.org/licenses/gpl-3.0.html.
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category MageINIC
 * @package MageINIC_CustomerProfile
 * @copyright Copyright (c) 2023 MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\CustomerProfile\Plugin\CustomerData;

use Magento\Customer\Helper\View;
use Magento\Customer\Helper\Session\CurrentCustomer;
use MageINIC\CustomerProfile\Block\Attributes\Profile;

/**
 * Class Customer Plugin
 */
class Customer
{
    /**
     * @var CurrentCustomer
     */
    protected CurrentCustomer $currentCustomer;

    /**
     * @var View
     */
    protected View $customerViewHelper;

    /**
     * @var Profile
     */
    public Profile $customerProfile;

    /**
     * Customer constructor
     *
     * @param CurrentCustomer $currentCustomer
     * @param View $customerViewHelper
     * @param Profile $customerProfile
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        View            $customerViewHelper,
        Profile         $customerProfile
    ) {
        $this->currentCustomer    = $currentCustomer;
        $this->customerViewHelper = $customerViewHelper;
        $this->customerProfile    = $customerProfile;
    }

    /**
     * @inheritdoc
     */
    public function afterGetSectionData(): array
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return [];
        }
        $customer = $this->currentCustomer->getCustomer();
        if (!empty($customer->getCustomAttribute('profile_picture'))) {
            $file = $customer->getCustomAttribute('profile_picture')->getValue();
        } else {
            $file = '';
        }
        return [
            'fullname' => $this->customerViewHelper->getCustomerName($customer),
            'firstname' => $customer->getFirstname(),
            'avatar' => $this->customerProfile->getCurrentCustomerProfile($file)
        ];
    }
}
