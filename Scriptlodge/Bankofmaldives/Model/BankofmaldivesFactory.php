<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bankofmaldives\Model;

/**
 * @api
 * @since 100.0.2
 */
class BankofmaldivesFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new Bankofmaldives model
     *
     * @param array $arguments
     * @return \Scriptlodge\Bankofmaldives\Model\Bankofmaldives
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\Scriptlodge\Bankofmaldives\Model\Bankofmaldives::class, $arguments);
    }
}
