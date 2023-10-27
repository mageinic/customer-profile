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

namespace MageINIC\CustomerProfile\Ui\Component\Listing\Columns;

use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Profile Of Ui Component
 */
class Profile extends Column
{
    /**
     * @var Repository|AbstractBlock
     */
    protected Repository|AbstractBlock $viewFileUrl;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;

    /**
     * Profile constructor
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param Repository         $viewFileUrl
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        Repository         $viewFileUrl,
        array              $components = [],
        array              $data = []
    ) {
        $this->urlBuilder   = $urlBuilder;
        $this->viewFileUrl  = $viewFileUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $customer = new DataObject($item);
                $picture_url = !empty($customer["profile_picture"]) ? $this->urlBuilder->getUrl(
                    'customer/index/viewfile/image/' .
                    base64_encode($customer["profile_picture"])
                ) : $this->viewFileUrl->getUrl(
                    'MageINIC_CustomerProfile::images/no-profile-photo.jpg'
                );
                $item[$fieldName . '_src'] = $picture_url;
                $item[$fieldName . '_orig_src'] = $picture_url;
                $item[$fieldName . '_alt'] = 'The profile picture';
            }
        }
        return $dataSource;
    }
}
