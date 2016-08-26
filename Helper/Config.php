<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Config extends AbstractHelper {

    
    
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Route\Config
     */
    protected $_routeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_frontendUrl;

    /**
     * @var string
     */
    protected $_downloadUrl;

    /**
     * @var array
     */
    private $_configCache;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_rootDirectory;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Route\Config $routeConfig
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
            \Magento\Framework\App\Route\Config $routeConfig,
            \Magento\Framework\Locale\ResolverInterface $locale,
            \Magento\Backend\Model\UrlInterface $backendUrl,
            \Magento\Framework\UrlInterface $frontendUrl,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Config\Model\ResourceModel\Config $resourceConfig,
            \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);

        $this->_scopeConfig = $scopeConfig;
        $this->_routeConfig = $routeConfig;
        $this->_locale = $locale;
        $this->_backendUrl = $backendUrl;
        $this->_frontendUrl = $frontendUrl;
        $this->_resourceConfig = $resourceConfig;
        $this->_rootDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);


        $this->_configCache = array();
    }



    /**
     * Get config for this module.
     * To get store specific config:
     * getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
     *
     * To avoid config reinitialization and stores reinitialization,
     * we are just setting new config values to cache if we need to get it back
     * in the same request.
     *
     * @param type $path
     * @return type
     */
    public function getConfig($path) {
        if (isset($this->_configCache[$path])) {
            return $this->_configCache[$path];
        }
        $this->_configCache[$path] = $this->_scopeConfig->getValue('glugox_pdf/' . $path, 'default');
        return $this->_configCache[$path];
    }


    /**
     * Set config for this module.
     * To set store specific config:
     * setValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
     *
     * To avoid config reinitialization and stores reinitialization,
     * we are just setting new config values to cache if we need to get it back
     * in the same request.
     *
     * @param type $path
     * @return \Glugox\PDF\Helper\Config
     */
    public function setConfig($path, $value) {
        $this->_configCache[$path] = $value;
        $this->_resourceConfig->saveConfig('glugox_pdf/' . $path, $value, 'default', 0);
        return $this;
    }


    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getBackendUrl($route = '', $params = []) {
        return $this->_backendUrl->getUrl($route, $params);
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getFrontendUrl($route = '', $params = []) {
        return $this->_frontendUrl->getUrl($route, $params);
    }



    /**
     * @return \string
     */
    public function getDisplayLogo() {
        return $this->getConfig('design/display_logo');
    }


    /**
     * Get logo image from db config only
     *
     * @return \string
     */
    public function getLogoImagePath() {


        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;

        // glugox pdf logo setting
        $storeLogoPath = $this->_scopeConfig->getValue('glugox_pdf/design/logo_src', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$storeLogoPath) {
            // default magento logo getting
            $storeLogoPath = $this->_scopeConfig->getValue('design/header/logo_src', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        $path = $folderName . '/' . $storeLogoPath;
        return $path;
    }


    /**
     * Wether to display store name in the header or not
     *
     * @return type
     */
    public function getDisplayStoreName(){
        return $this->getConfig('design/display_store_name');
    }

    /**
     *
     * @return string
     */
    public function getStoreName() {

        $storeName = $this->getConfig('design/store_name');
        if(empty($storeName)){
            $storeName = $this->_scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return $storeName;
    }


    /**
     *
     * @return boolean
     */
    public function getDisplayPrice() {
        return (boolean) $this->getConfig('design/display_price');;
    }

    /**
     *
     * @return boolean
     */
    public function getDisplayCategories() {
        return (boolean) $this->getConfig('design/display_categories');;
    }

    /**
     *
     * @return boolean
     */
    public function getDisplaySku() {
        return (boolean) $this->getConfig('design/display_sku');
    }

    /**
     *
     * @return boolean
     */
    public function getDisplayDescriptionInSingleMode() {
        return (boolean) $this->getConfig('design/display_description_in_single_mode');
    }

    /**
     *
     * @return boolean
     */
    public function getDisplayAttributesInSingleMode() {
        return (boolean) $this->getConfig('design/display_attributes_in_single_mode');
    }




    /**
     * @return int
     */
    public function getThemeId() {
        return $this->_scopeConfig->getValue('design/theme/theme_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     *
     * @return string
     */
    public function getLocale() {
        return $this->_scopeConfig->getValue(\Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     *
     * @return \int
     */
    public function getDrawHeaderOnEachPage() {
        return $this->getConfig('design/header_on_each_page');
    }


    /**
     *
     * @return \int
     */
    public function getPdfPagePadding() {
        return 0;
    }


    /**
     *
     * @return \int
     */
    public function getPdfBodyPadding() {
        return (string) $this->getConfig('design/body_padding');
    }


    /**
     *
     * @return \int
     */
    public function getLogoHeight() {
        return (int) $this->getConfig('design/logo_height');
    }


    /**
     *
     * @return \int
     */
    public function getImageMaxWidthOnProductList() {
        return (int) $this->getConfig('image/list_image_max_width');
    }

    /**
     *
     * @return \int
     */
    public function getImageMaxHeightOnProductList() {
        return (int) $this->getConfig('image/list_image_max_height');
    }


    /**
     *
     * @return \int
     */
    public function getMaxNumberOfProducts() {
        return (int)$this->getConfig('general/max_items_on_list');
    }


    /**
     *
     * @return \int
     */
    public function getPdfTitleFontSize() {
        return (int)$this->getConfig('typography/single_title_size');
    }


    /**
     *
     * @return \int
     */
    public function getPdfItemTitleFontSize() {
        return (int)$this->getConfig('typography/list_title_size');
    }



    /**
     * @param \string $colorDefinition
     * @return \string
     */
    public function getPdfColor($colorDefinition) {
        return $this->getConfig('typography/' . $colorDefinition);
    }


    /**
     * Show image in single mode
     *
     * @return boolean
     */
    public function getShowImageInSingleMode(){
        return (boolean) $this->getConfig('image/show_image_in_single_mode');
    }

    /**
     * Singe image maximum width
     *
     * @return boolean
     */
    public function getSingleImageMaxWidth(){
        return (int) $this->getConfig('image/single_image_max_width');
    }
    /**
     * Singe image maximum height
     *
     * @return boolean
     */
    public function getSingleImageMaxHeight(){
        return (int) $this->getConfig('image/single_image_max_height');
    }


    /**
     * Show image in list mode
     *
     * @return boolean
     */
    public function getShowImageInListMode(){
        return (boolean) $this->getConfig('image/show_image_in_list_mode');
    }

    /**
     * List image maximum width
     *
     * @return boolean
     */
    public function getListImageMaxWidth(){
        return (int) $this->getConfig('image/list_image_max_width');
    }
    /**
     * List image maximum height
     *
     * @return boolean
     */
    public function getListImageMaxHeight(){
        return (int) $this->getConfig('image/list_image_max_height');
    }

    /**
     * Show button to generate pdf on product view pages
     *
     * @return boolean
     */
    public function getAllowedOnProductPages(){
        return (boolean) $this->getConfig('availability/allowed_on_product_pages');
    }

    /**
     * Show button to generate pdf on category view pages
     *
     * @return boolean
     */
    public function getAllowedOnCategoryPages(){
        return (boolean) $this->getConfig('availability/allowed_on_category_pages');
    }

    /**
     * Show button to generate pdf on anchor categories
     *
     * @return boolean
     */
    public function getAllowedOnAnchorCategories(){
        return (boolean) $this->getConfig('availability/allowed_on_anchor_categories');
    }

    /**
     * Show button to generate pdf on non anchor categories
     *
     * @return boolean
     */
    public function getAllowedOnNonAnchorCategories(){
        return (boolean) $this->getConfig('availability/allowed_on_non_anchor_categories');
    }


    /**
     * Restrict pdf button on frontend for specific customer groups.
     * Null means allowed for all
     *
     * @return boolean
     */
    public function getAllowedCustomerGroups(){

        $groups = $this->getConfig('availability/allowed_customer_groups');
        if($groups){
            return \explode(",", $groups);
        }
        return null;
    }

    /**
     * Restrict pdf button on frontend for specific customers (ids).
     * Null means allowed for all
     *
     * @return boolean
     */
    public function getAllowedCustomerIds(){
        $ids = $this->getConfig('availability/allowed_customers');
        if($ids){
            return \explode(",", $ids);
        }
        return null;
    }


}
