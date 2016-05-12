<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Glugox\PDF\Model\ResourceModel\PDF;

/**
 * PDFs collection.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_idFieldName = \Glugox\PDF\Model\PDF::PDF_ID;

    /**
     * Resource collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Glugox\PDF\Model\PDF', 'Glugox\PDF\Model\ResourceModel\PDF');
    }

}
