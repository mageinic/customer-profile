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
 * phpcs:ignoreFile
 */

namespace MageINIC\CustomerProfile\ViewModel;

use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Http\Context;
use Magento\Framework\View\Asset\Repository;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MageINIC\CustomerProfile\Helper\Data;

/**
 * Class Profile Of View Model
 */
class Profile implements ArgumentInterface
{
    /**
     * @var UrlInterface
     */
    private UrlInterface $urlInterface;

    /**
     * @var Repository
     */
    private Repository $viewFileUrl;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Data
     */
    private Data $customerProfileHelper;

    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * Profile Constructor
     *
     * @param Context $context
     * @param Repository $viewFileUrl
     * @param CustomerFactory $customerFactory
     * @param Filesystem $filesystem
     * @param Storage $storage
     * @param UrlInterface $urlInterface
     * @param Data $customerProfileHelper
     */
    public function __construct(
        Context         $context,
        Repository      $viewFileUrl,
        CustomerFactory $customerFactory,
        Filesystem      $filesystem,
        Storage         $storage,
        UrlInterface    $urlInterface,
        Data            $customerProfileHelper
    ) {
        $this->urlInterface = $urlInterface;
        $this->viewFileUrl = $viewFileUrl;
        $this->storage = $storage;
        $this->filesystem = $filesystem;
        $this->customerFactory = $customerFactory;
        $this->customerProfileHelper = $customerProfileHelper;
    }

    /**
     * Check Profile Image File
     *
     * @param string $file
     * @return bool
     */
    public function checkProfileImageFile(string $file): bool
    {
        $file = base64_decode($file);
        $filesystem = $this->filesystem;
        $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . ltrim($file, '/');
        $path = $directory->getAbsolutePath($fileName);
        if (!$directory->isFile($fileName)
            && !$this->storage->processStorageFile($path)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Get Current Customer Profile
     *
     * @param string $file
     * @return string
     */
    public function getCurrentCustomerProfile(string $file): string
    {
        if ($this->checkProfileImageFile(base64_encode($file)) === true) {
            return $this->urlInterface->getUrl(
                'viewfile/profile/view/',
                ['image' => base64_encode($file)]
            );
        }
        return $this->viewFileUrl->getUrl(
            'MageINIC_CustomerProfile::images/no-profile-photo.jpg'
        );
    }

    /**
     * Get Customer Profile Image I'd
     *
     * @param bool $customer_id
     * @return string
     */
    public function getCustomerProfileImageById($customer_id = false)
    {
        if ($customer_id) {
            $customerDetail = $this->customerFactory->create()->load($customer_id);
            if ($customerDetail && !empty($customerDetail->getProfilePicture())) {
                if ($this->checkProfileImageFile(
                    base64_encode($customerDetail->getProfilePicture())
                ) === true) {
                    return $this->urlInterface->getUrl(
                        'viewfile/profile/view/',
                        [
                            'image' => base64_encode($customerDetail->getProfilePicture())
                        ]
                    );
                }
            }
        }
    }

    /**
     * Get Customer Profile Helper Data
     *
     * @return Data
     */
    public function getCustomerProfileHelper(): Data
    {
        return $this->customerProfileHelper;
    }
}
