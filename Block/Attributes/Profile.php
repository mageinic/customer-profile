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

namespace MageINIC\CustomerProfile\Block\Attributes;

use Magento\Customer\Model\Customer;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Helper\File\Storage;
use MageINIC\CustomerProfile\Helper\Data;

/**
 * Class Profile Block
 */
class Profile extends Template
{
    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Customer
     */
    protected Customer $customer;

    /**
     * @var Repository
     */
    private Repository $viewFileUrl;

    /**
     * Profile constructor.
     * @param Context $context
     * @param Repository $viewFileUrl
     * @param Customer $customer
     * @param Filesystem $filesystem
     * @param Storage $storage
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Repository $viewFileUrl,
        Customer $customer,
        Filesystem $filesystem,
        Storage $storage,
        Data $helper,
    ) {
        $this->storage = $storage;
        $this->filesystem = $filesystem;
        $this->customer = $customer;
        $this->viewFileUrl = $viewFileUrl;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Check Image File
     *
     * @param string $file
     * @return bool
     */
    public function checkImageFile(string $file): bool
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
        if ($this->checkImageFile(base64_encode($file)) === true) {
            return $this->getUrl('viewfile/profile/view/',
                ['image' => base64_encode($file)]
            );
        }
        return $this->viewFileUrl->getUrl(
            'MageINIC_CustomerProfile::images/no-profile-photo.jpg'
        );
    }
}
