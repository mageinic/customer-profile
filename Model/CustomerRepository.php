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

namespace MageINIC\CustomerProfile\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Exception\State\InputMismatchException;
use MageINIC\CustomerProfile\Api\CustomerRepositoryInterface as MageINICCustomerRepositoryInterface;

/**
 * Class Of Customer Repository
 */
class CustomerRepository implements MageINICCustomerRepositoryInterface
{
    /**#@+
     * Constants For Module Customer Profile
     */
    public const PROFILE_PICTURE  = 'profile_picture';
    public const CUSTOMER_PROFILE = 'customer';
    /**#@-*/

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @var ImageContentInterfaceFactory
     */
    private ImageContentInterfaceFactory $imageContentInterfaceFactory;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var ImageProcessorInterface
     */
    private ImageProcessorInterface $imageProcessor;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepositoryData;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var File
     */
    private File $file;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * Customer Repository constructor
     *
     * @param Json                         $serializer
     * @param ImageContentInterfaceFactory $imageContentInterfaceFactory
     * @param RequestInterface             $request
     * @param ImageProcessorInterface      $imageProcessor
     * @param CustomerRepositoryInterface  $customerRepositoryData
     * @param StoreManagerInterface        $storeManager
     * @param File                         $file
     * @param Filesystem                   $filesystem
     */
    public function __construct(
        Json                         $serializer,
        ImageContentInterfaceFactory $imageContentInterfaceFactory,
        RequestInterface             $request,
        ImageProcessorInterface      $imageProcessor,
        CustomerRepositoryInterface  $customerRepositoryData,
        StoreManagerInterface        $storeManager,
        File                         $file,
        Filesystem                   $filesystem
    ) {
        $this->imageContentInterfaceFactory = $imageContentInterfaceFactory;
        $this->serializer                   = $serializer;
        $this->request                      = $request;
        $this->imageProcessor               = $imageProcessor;
        $this->customerRepositoryData       = $customerRepositoryData;
        $this->storeManager                 = $storeManager;
        $this->file                         = $file;
        $this->filesystem                   = $filesystem;
    }

    /**
     * Customer Profile Upload
     *
     * @param int $customerId
     * @return mixed|string
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function customerProfileUpload($customerId): mixed
    {
        try {
            $valid = $this->validatedParams();
            if ($valid) {
                $content = $valid;
                $data = $this->serializer->unserialize($content);
                $imageInterface = $this->imageContentInterfaceFactory->create();
                $imageInterface->setBase64EncodedData($data[ImageContentInterface::BASE64_ENCODED_DATA]);
                $imageInterface->setType($data[ImageContentInterface::TYPE]);
                $imageInterface->setName($data[ImageContentInterface::NAME]);
            }
            $result = $this->save($imageInterface, $customerId);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException($e->getMessage());
        }
        return $result;
    }

    /**
     * Validate Params Function
     *
     * @return mixed
     * @throws LocalizedException
     */
    private function validatedParams(): mixed
    {
        $request = $this->request->getContent();
        $checkKeyExits = $this->serializer->unserialize($request);
        if (!array_key_exists(ImageContentInterface::BASE64_ENCODED_DATA, $checkKeyExits) ||
            trim($checkKeyExits[ImageContentInterface::BASE64_ENCODED_DATA]) === ''
        ) {
            throw new LocalizedException(__('Enter the base64 encoded data key and value try again.'));
        }
        if (!array_key_exists(ImageContentInterface::NAME, $checkKeyExits) ||
            trim($checkKeyExits[ImageContentInterface::NAME]) === ''
        ) {
            throw new LocalizedException(__('Enter the name key and value try again.'));
        }
        if (!array_key_exists(ImageContentInterface::TYPE, $checkKeyExits) ||
            trim($checkKeyExits[ImageContentInterface::TYPE]) === ''
        ) {
            throw new LocalizedException(__('Enter the type key and value try again.'));
        }
        return $request;
    }

    /**
     * Save Customer Profile Function
     *
     * @param mixed $imageInterface
     * @param int $id
     * @return mixed|string
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function save(mixed $imageInterface, int $id): mixed
    {
        $result = "";
        $mediaPath = self::CUSTOMER_PROFILE;
        $relativeFilePath = $this->imageProcessor->processImageContent($mediaPath, $imageInterface);
        $customer = $this->customerRepositoryData->getById($id);
        if (!array_key_exists(self::PROFILE_PICTURE, $customer->getCustomAttributes())) {
            $customer->setCustomAttribute(self::PROFILE_PICTURE, $relativeFilePath);
        }

        $profilePicture = $customer->getCustomAttribute(self::PROFILE_PICTURE);
        $profilePicture->setValue($relativeFilePath);
        $this->customerRepositoryData->save($customer);

        if ($profilePicture->getValue()) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . self::CUSTOMER_PROFILE;
            $profilePicture->setValue($this->getFilePath($mediaUrl, $profilePicture->getValue()));

            $result = $profilePicture->getValue();
        }
        return $result;
    }

    /**
     * Get File Path
     *
     * @param string $path
     * @param string $imageName
     * @return string
     */
    public function getFilePath(string $path, string $imageName): string
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }

    /**
     * Remove Profile Upload
     *
     * @param int $customerId
     * @return bool
     * @throws LocalizedException
     */
    public function removeProfileUpload($customerId): bool
    {
        try {
            $customer = $this->customerRepositoryData->getById($customerId);
            if (array_key_exists(self::PROFILE_PICTURE, $customer->getCustomAttributes())) {
                $profilePicture = $customer->getCustomAttribute(self::PROFILE_PICTURE);
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $mediaRootDir = $mediaDirectory->getAbsolutePath() . self::CUSTOMER_PROFILE;
                if ($this->file->isExists($mediaRootDir . $profilePicture->getValue())) {
                    $this->file->deleteFile($mediaRootDir . $profilePicture->getValue());
                }
                $customer->setCustomAttribute(self::PROFILE_PICTURE, '');
            }
            $this->customerRepositoryData->save($customer);
            return true;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Something wants wrong not able to remove profile picture.'));
        }
    }
}
