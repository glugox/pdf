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
use Glugox\PDF\Model\PDFResult;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper {

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $_rootDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_staticDirectory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig
     */
    protected $_catalogProductMediaConfig;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_fileStorageHelper;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeFactory
     */
    private $_themeFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Factory
     */
    protected $_priceInfoFactory;

    /**
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     *
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /** @var \Magento\Framework\App\State */
    protected $_state;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     *
     * @var \Glugox\PDF\Model\Provider\Products\ProviderInterface
     */
    protected $_productsProvider;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeFactory
     * @param \Magento\Framework\Pricing\PriceInfo\Factory $priceInfoFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
            \Magento\Framework\ObjectManagerInterface $objectManager,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
            \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper,
            \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeFactory,
            \Magento\Framework\Pricing\PriceInfo\Factory $priceInfoFactory,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
            \Magento\Framework\App\State $state,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\App\RequestInterface $request,
            \Glugox\PDF\Model\Provider\Products\ProviderInterface $productsProvider
    ) {
        parent::__construct($context);

        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        $this->_rootDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_staticDirectory = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->_catalogProductMediaConfig = $catalogProductMediaConfig;
        $this->_fileStorageHelper = $fileStorageHelper;
        $this->_themeFactory = $themeFactory;
        $this->_priceInfoFactory = $priceInfoFactory;
        $this->_productRepository = $productRepository;
        $this->_categoryRepository = $categoryRepository;
        $this->_state = $state;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_productsProvider = $productsProvider;

        $this->_productsProvider->setHelper($this);
    }


    /**
     *
     * @param type $code
     * @return \Glugox\PDF\Helper\Data
     */
    public function setAreaCode($code) {
        $this->_state->setAreaCode($code);
        return $this;
    }


    /**
     *
     * @return int
     */
    public function getCurrentStoreId() {
        return $this->_storeManager->getStore()->getId();
    }


    /**
     * @return \Glugox\PDF\Helper\Config
     */
    public function getConfigObject() {
        return $this->_objectManager->get("Glugox\PDF\Helper\Config");
    }


    /**
     * Get current customer session
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getSession() {
        return $this->_objectManager->get("Magento\Customer\Model\Session");
    }


    /**
     * Retrieve cached object instance
     *
     * @param string $class
     * @return type
     */
    public function getInstance($class) {
        return $this->_objectManager->get($class);
    }


    /**
     * Create new object instance
     *
     * @param string $class
     * @return type
     */
    public function createInstance($class) {
        return $this->_objectManager->create($class);
    }


    /**
     *
     * @return PDFResult
     */
    public function createPdfResult(){
        return $this->createInstance("Glugox\PDF\Model\PDFResult");
    }

    /**
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest() {
        return $this->_request;
    }


    /**
     * Returns PDF provider
     *
     * @return \Glugox\PDF\Model\Provider\PDF\ProviderInterface
     */
    public function getPDFProvider() {
        return $this->_objectManager->get("Glugox\PDF\Model\Provider\PDF\ProviderInterface");
    }
    


    /**
     * Returns current frontend theme model
     *
     * @return \Magento\Theme\Model\Theme Theme
     */
    public function getCurrentTheme() {

        $themeIdentifier = $this->getConfigObject()->getThemeId();
        $themeCollection = $this->_themeFactory->create();
        if (is_numeric($themeIdentifier)) {
            $theme = $themeCollection->getItemById($themeIdentifier);
        } else {
            $themeFullPath = $area . \Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR . $themeIdentifier;
            $theme = $themeCollection->getThemeByFullPath($themeFullPath);
        }

        return $theme;
    }


    /**
     *
     * @return type
     */
    public function getRegisteredProduct($id = null) {
        if (!$this->_registry->registry('pdf_product') && $id) {
            $product = $this->_productRepository->getById($id);
            $this->_registry->register('pdf_product', $product);
        }
        return $this->_registry->registry('pdf_product');
    }


    /**
     *
     * @return type
     */
    public function getRegisteredCategory($id = null) {
        if (!$this->_registry->registry('pdf_category') && $id) {
            $category = $this->_categoryRepository->get($id);
            $this->_registry->register('pdf_category', $category);
        }
        return $this->_registry->registry('pdf_category');
    }


    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Glugox\PDF\Helper\Data
     */
    public function setRegisteredLayerCategory(\Magento\Catalog\Model\Category $category) {
        $this->info("Setting current category: " . $category->getId());
        $currentCategory = $this->_registry->registry('current_category');
        if (!$currentCategory) {
            $this->_registry->register('current_category', $category);
        } elseif ($currentCategory && $currentCategory->getId() !== $category->getId()) {
            throw new \Glugox\PFD\Exception\PDFException(__("Trying to change current category in registry!"));
        }

        return $this;
    }


    /**
     * @return \Magento\Catalog\Model\Category $category
     */
    public function getRegisteredLayerCategory() {
        return $this->_registry->registry('current_category');
    }


    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPrintPdfUrl(\Magento\Catalog\Model\Product $product) {
        return $this->getFrontendUrl("pdf/product/download", array("id" => $product->getId())) . '?price=50-60';
    }


    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getCategoryPrintPdfUrl(\Magento\Catalog\Model\Category $category) {

        $url = $this->getFrontendUrl("pdf/category/download", array("id" => $category->getId()));
        $layeredParams = $this->_request->getQuery()->toString();
        if (!empty($layeredParams)) {
            $url = $url . '?' . $layeredParams;
        }
        return $url;
    }


    /**
     *
     * @return string|null
     */
    public function getLogoImagePath() {

        $path = $this->getConfigObject()->getLogoImagePath();

        if ($this->_fileStorageHelper->checkDbUsage() && !$this->_mediaDirectory->isFile($path)) {
            $this->_fileStorageHelper->saveFileToFilesystem($path);
        }

        if (!$path || !$this->_mediaDirectory->isFile($path)) {

            if (\extension_loaded('imagick')) {

                // try to convert the svg default format to
                // zend_pdf compatible png format
                try {
                    $themeImagesPath = $this->_staticDirectory->getAbsolutePath($this->getCurrentTheme()->getFullPath() . '/' . $this->getConfigObject()->getLocale());
                    $themeLogoPath = $themeImagesPath . '/images/logo.svg';
                    $im = new \Imagick();
                    $im->setBackgroundColor(new \ImagickPixel('transparent'));
                    $svg = \file_get_contents($themeLogoPath);
                    $im->readImageBlob($svg);
                    $im->setImageFormat("png32");
                    $im->writeimage($themeImagesPath . '/images/logo.png');
                    return $themeImagesPath . '/images/logo.png';
                } catch (\ImagickException $ex) {
                    //
                }
            }

            return null;
        } else {
            return $this->_mediaDirectory->getAbsolutePath($path);
        }
    }


    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param type $type
     * @return type
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product,
            $type) {
        return $this->_priceInfoFactory->create($product)->getPrice($type);
    }


    /**
     *
     * @return string
     */
    public function getStoreName() {
        return $this->getConfigObject()->getStoreName();
    }


    /**
     * Image to be displayed for a product in pdf
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductImage(\Magento\Catalog\Model\Product $product) {
        return $this->_mediaDirectory->getAbsolutePath($this->_catalogProductMediaConfig->getBaseMediaPath() . $product->getImage());
    }


    /**
     * @return string
     */
    public function getDownloadUrl() {
        return $this->getConfigObject()->getDownloadUrl();
    }


    /**
     * @return string
     */
    public function getStoragePath($fileName = NULL) {
        return $this->_rootDirectory->getAbsolutePath($this->getConfigObject()->getStorageRelativePath() . (!$fileName ? '' : '/' . $fileName));
    }


    /**
     * If we do not display any elements in the header,
     * we wont display the header container as well
     */
    public function getDisplayHeader() {
        return !(!$this->getConfigObject()->getDisplayLogo() && !$this->getConfigObject()->getDisplayStoreName());
    }


    /**
     * Wether we can show the pdf generate button on frontpage
     * defined by customer group or customer id
     */
    public function canDisplayToCustomer() {

        $customerId = (int) $this->getSession()->getCustomerId();
        $customerGroupId = (int) $this->getSession()->getCustomerGroupId();

        $allowedCustomerGroups = $this->getConfigObject()->getAllowedCustomerGroups();
        $allowedCustomerIds = $this->getConfigObject()->getAllowedCustomerIds();
        if (null === $allowedCustomerGroups && null === $allowedCustomerIds) {
            return true;
        }

        if ($allowedCustomerGroups && !\in_array($customerGroupId, $allowedCustomerGroups)) {
            return false;
        }
        if ($allowedCustomerIds && \in_array($customerId, $allowedCustomerIds)) {
            return false;
        }

        return true;
    }


    /**
     * Wether we can show the pdf generate button on frontpage
     * defined by config values
     */
    public function canDisplayOnProductPages() {
        return $this->getConfigObject()->getAllowedOnProductPages();
    }


    /**
     *
     * @return boolean
     */
    public function canDisplayOnProduct(\Magento\Catalog\Model\Product $product) {
        if (!$this->canDisplayToCustomer()) {
            return false;
        }
        if (!$this->canDisplayOnProductPages()) {
            return false;
        }
        return true;
    }


    /**
     * Wether we can show the pdf generate button on frontpage
     * defined by config values
     */
    public function canDisplayOnCategoryPages() {
        return $this->getConfigObject()->getAllowedOnCategoryPages();
    }


    /**
     *
     * @return boolean
     */
    public function canDisplayOnCategory(\Magento\Catalog\Model\Category $category) {
        if (!$this->canDisplayToCustomer()) {
            return false;
        }
        if (!$this->canDisplayOnCategoryPages()) {
            return false;
        }
        if ($category->getIsAnchor()) {
            return $this->getConfigObject()->getAllowedOnAnchorCategories();
        } else {
            return $this->getConfigObject()->getAllowedOnNonAnchorCategories();
        }
    }


    /**
     * Returns number of products that will the print putton execute for printing
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return int
     */
    public function getEstimatePrintProductsOnCategory(\Magento\Catalog\Model\Category $category) {
        return $this->_productsProvider->getProductCountByCategories([$category]);
    }


    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger() {
        return $this->_logger;
    }


    /**
     *
     * @param type $path
     * @return type
     */
    public function getConfig($path) {
        return $this->getConfigObject()->getConfig($path);
    }


    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getBackendUrl($route = '', $params = []) {
        return $this->getConfigObject()->getBackendUrl($route, $params);
    }


    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getFrontendUrl($route = '', $params = []) {
        return $this->getConfigObject()->getFrontendUrl($route, $params);
    }


    /**
     *
     * @param type $path
     * @param type $value
     * @return type
     */
    public function setConfig($path, $value) {
        return $this->getConfigObject()->setConfig($path, $value);
    }


    /**
     * @param string $route
     * @param array $params
     * @return null
     */
    public function info($message, $isError = false) {

        $output = null;
        if (empty($message)) {
            return false;
        }
        if (!is_string($message)) {
            $message = '<pre>' . print_r($message, true) . '</pre>';
        }

        if ($isError) {
            $color = "#ff0000";
            $message = "ERROR: " . $message;
        }

        /* if ($color) {
          $message = '<span style="color:' . $color . '">' . $message . '</span>';
          } */

        // if we are running command, we have set the command output interface in the registry from that command
        if (null !== ($this->_registry->registry(\Glugox\PDF\Console\Command\CreateCommand::CURRENT_CMD_OUTPUT_INTERFACE))) {
            $output = $this->_registry->registry(\Glugox\PDF\Console\Command\CreateCommand::CURRENT_CMD_OUTPUT_INTERFACE);
            if ("." === $message) {
                $output->write('.');
                return null;
            }
            $output->writeln('<info>' . $message . '</info>');
        }

        return $this->_logger->info($message);
    }


    /**
     * Products provider
     *
     * @return \Glugox\PDF\Model\Provider\Products\ProviderInterface
     */
    public function getProductsProvider() {
        return $this->_productsProvider;
    }


}
