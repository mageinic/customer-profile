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

namespace MageINIC\CustomerProfile\Plugin\Metadata\Form;

use MageINIC\CustomerProfile\Model\Source\Validation\Image as SourceModelImage;
use Magento\Customer\Model\Metadata\Form\Image as CustomerMetaImage;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\Request;

/**
 * Class Plugin Image
 */
class Image
{
    /**
     * @var Image|SourceModelImage
     */
    protected Image|SourceModelImage $validImage;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * Image constructor
     *
     * @param SourceModelImage $validImage
     * @param Request $request
     */
    public function __construct(
        SourceModelImage $validImage,
        Request $request
    ) {
        $this->validImage = $validImage;
        $this->request = $request;
    }

    /**
     * Before Extract Value Method
     *
     * @param CustomerMetaImage $subject
     * @param array $value
     * @return array
     * @throws LocalizedException
     */
    public function beforeExtractValue(CustomerMetaImage $subject, $value): array
    {
        $attrCode = $subject->getAttribute()->getAttributeCode();
        $files = $this->request->getFiles()->toArray();
        if ($this->validImage->isImageValid('tmp_name', $attrCode) === false) {
            $files[$attrCode]['tmp_image_name'] = $files[$attrCode]['tmp_name'];
            unset($files[$attrCode]['tmp_name']);
        }
        return [$value];
    }
}
