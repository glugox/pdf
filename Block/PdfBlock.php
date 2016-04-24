<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Block;

class PdfBlock extends \Magento\Framework\View\Element\Template
{


    public function __construct(
            \Magento\Framework\Registry $registry,
            \Magento\Framework\View\Element\Template\Context $context,
            \Glugox\PDF\Helper\Data $helper,
            array $data = array()
            ) {


        parent::__construct($context, $data);
    }



}
