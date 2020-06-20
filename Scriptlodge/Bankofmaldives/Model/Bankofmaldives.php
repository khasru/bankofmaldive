<?php

namespace Scriptlodge\Bankofmaldives\Model;


class Bankofmaldives extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Scriptlodge\Bankofmaldives\Model\ResourceModel\Bankofmaldives::class);
    }
}
