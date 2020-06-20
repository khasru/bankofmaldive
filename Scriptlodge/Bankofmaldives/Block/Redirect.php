<?php
namespace Scriptlodge\Bankofmaldives\Block;

use Magento\Framework\Controller\ResultFactory;


class Redirect extends \Magento\Framework\View\Element\Template
{

    protected $_logo;
    protected $_bmlHelper;
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;
    /**
     * @var array
     */
    private $postData = null;

    protected $resultFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Scriptlodge\Bankofmaldives\Helper\Data $bmlHelper,
        ResultFactory $resultFactory,
        array $data = []
    )
    {
        $this->_logo = $logo;
        $this->_bmlHelper = $bmlHelper;
        $this->resultFactory=$resultFactory;
        parent::__construct($context, $data);
    }

    public function getOrderFormData(){

     return  $requestData=$this->_bmlHelper->getResponseValue(); 

    }
}