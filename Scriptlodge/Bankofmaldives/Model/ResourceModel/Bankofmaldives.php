<?php

namespace Scriptlodge\Bankofmaldives\Model\ResourceModel;


class Bankofmaldives extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Table with localized region names
     *
     * @var string
     */
    protected $_regionNameTable;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Define main and locale region name tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bankofmaldives', 'entity_id');

    }
}