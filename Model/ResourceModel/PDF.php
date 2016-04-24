<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\ResourceModel;

/**
 * PDF resource model
 */
class PDF extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('glugox_pdf', 'pdf_id');
    }

    /**
     * Retrieve pdf by index data fields
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param array $pdfData
     * @return \Glugox\PDF\Model\ResourceModel\PDF
     */
    public function getByIndexData( \Magento\Framework\Model\AbstractModel $object, array $pdfData ){

        $connection = $this->getConnection();
        if ($connection ) {

            $select = $connection->select()->from($this->getMainTable());
            foreach ($pdfData as $field => $value){
                $field = $connection->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
                $select->where($field . '=?', $value);
            }
            $data = $connection->fetchRow($select);
            if ($data) {
                $object->setData($data);
            }
        }
        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

}
