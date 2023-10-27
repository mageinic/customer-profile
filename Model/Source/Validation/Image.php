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

namespace MageINIC\CustomerProfile\Model\Source\Validation;

use Magento\Framework\HTTP\PhpEnvironment\Request;

/**
 * Class Image Of Source
 */
class Image
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * Validate constructor.
     *
     * @param Request $request
     */
    public function __construct(
        Request $request
    ) {
        $this->request = $request;
    }
    /**
     * Image Valid Function
     *
     * @param string $tmp_name
     * @param string $attrCode
     * @return bool
     */

    public function isImageValid(string $tmp_name, string $attrCode)
    {
        $files = $this->request->getFiles()->toArray();
        if ($attrCode == 'profile_picture') {
            if (!empty($files[$attrCode][$tmp_name])) {
                $imageFile = @getimagesize($files[$attrCode][$tmp_name]);
                if ($imageFile === false) {
                    return false;
                } else {
                    $valid_types = ['image/gif', 'image/jpeg', 'image/jpg', 'image/png'];
                    if (!in_array($imageFile['mime'], $valid_types)) {
                        return false;
                    }
                }
                return true;
            }
        }
        return true;
    }
}
