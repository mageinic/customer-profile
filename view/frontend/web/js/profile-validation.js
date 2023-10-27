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

require([
    'jquery',
    "mage/url",
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($, urlBuilder) {
    'use strict';
    var imageLoader = document.getElementById('profile_picture');
    imageLoader.addEventListener('change', handleImage, false);

    function handleImage(e) {
        var reader = new FileReader();
        reader.onload = function (event) {
            $('.uploader img').attr('src', event.target.result);
        };
        reader.readAsDataURL(e.target.files[0]);
    }

    $('.remove-image').click(function () {
        var url = urlBuilder.build('viewfile/profile/delete');
        var customerid = $('.customer_id').val();
        var no_profile = $('.no-profile').val();
        $.ajax({
            url: url ,
            type: 'POST',
            dataType: 'text',
            data: {customerid: customerid},
            success: function (response) {
                if (response === 'true') {
                    $('.profile-image').attr('src', no_profile);
                } else {
                    alert('Failed to remove the image.');
                }
            }
        });
    });
});
