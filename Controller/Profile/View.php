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

namespace MageINIC\CustomerProfile\Controller\Profile;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Url\DecoderInterface;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class View Profile Controller
 */
class View extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var File
     */
    protected File $file;

    /**
     * @var RawFactory
     */
    protected RawFactory $resultRawFactory;

    /**
     * @var DecoderInterface
     */
    protected DecoderInterface $urlDecoder;

    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var Storage
     */
    protected Storage $storage;

    /**
     * View constructor
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param DecoderInterface $urlDecoder
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param Storage $storage
     * @param File $file
     */
    public function __construct(
        Context          $context,
        RawFactory       $resultRawFactory,
        DecoderInterface $urlDecoder,
        FileFactory      $fileFactory,
        Filesystem       $filesystem,
        Storage          $storage,
        File             $file
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->urlDecoder = $urlDecoder;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->storage = $storage;
        $this->file = $file;
        return parent::__construct($context);
    }

    /**
     * Contoller View Execute
     *
     * @return ResponseInterface|Raw|ResultInterface
     * @throws NotFoundException
     * @throws FileSystemException
     */
    public function execute(): ResultInterface|ResponseInterface|Raw
    {
        $file = null;
        $plain = false;
        if ($this->getRequest()->getParam('file')) {
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('file')
            );
        } elseif ($this->getRequest()->getParam('image')) {
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('image')
            );
            $plain = true;
        } else {
            throw new NotFoundException(__('Page not found.'));
        }

        $filesystem = $this->filesystem;
        $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . ltrim($file, '/');
        $path = $directory->getAbsolutePath($fileName);

        if (!$directory->isFile($fileName)
                && !$this->storage->processStorageFile($path)
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        if ($plain) {
            $extension = $this->file->getPathInfo($path, PATHINFO_EXTENSION);
            switch ($extension) {
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }
            $stat = $directory->stat($fileName);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];

            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($directory->readFile($fileName));
            return $resultRaw;
        } else {
            $name = $this->file->getPathInfo($path, PATHINFO_BASENAME);
            $this->fileFactory->create(
                $name,
                ['type' => 'filename', 'value' => $fileName],
                DirectoryList::MEDIA
            );
        }
    }
}
