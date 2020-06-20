<?php

namespace Scriptlodge\Bankofmaldives\Model\ResourceModel\Bankofmaldives;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct() {
        $this->_init('Scriptlodge\Bankofmaldives\Model\Bankofmaldives', 'Scriptlodge\Bankofmaldives\Model\ResourceModel\Bankofmaldives');
        $this->addOrder('entity_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
    }
}
