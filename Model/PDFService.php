<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model;

use Glugox\PDF\Model\PDFFactory;
use Glugox\PDF\Model\PDF as PDFModel;
use Glugox\PDF\Model\PDFResult;
use Glugox\PDF\Exception\PDFException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputDefinition;
use Glugox\PDF\Api\PDFServiceInterface;
use Glugox\PDF\Console\Command\CreateCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * PDF Service.
 *
 * This service is used to interact with pdfs.
 */
class PDFService implements PDFServiceInterface {


    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    /** @var \Symfony\Component\Console\Input\StringInput */
    protected $_input;

    /** @var PDFFactory */
    protected $_pdfFactory;

    /** @var \Glugox\PDF\Helper\Data */
    protected $_helper;

    /** @var \Glugox\PDF\Model\Provider\PDF\ProviderInterface */
    protected $_pdfProvider;

    /**
     * @var Cache
     */
    protected $_cache;

    /**
     * @var Page\Context
     */
    protected $_context;

    /**
     * @var Page\Config
     */
    protected $_pageConfig;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     *
     * @param PDFFactory $pdfFactory
     * @param \Glugox\PDF\Helper\Data $helper
     */
    public function __construct(
            \Glugox\PDF\Model\Page\Context $context,
            PDFFactory $pdfFactory,
            \Glugox\PDF\Helper\Data $helper,
            \Glugox\PDF\Model\Provider\PDF\ProviderInterface $pdfProvider,
            \Glugox\PDF\Model\Cache $cache,
            \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->_context = $context;
        $this->_pdfFactory = $pdfFactory;
        $this->_helper = $helper;
        $this->_pdfProvider = $pdfProvider;
        $this->_cache = $cache;
        $this->_pageConfig = $context->getPageConfig();
        $this->categoryRepository = $categoryRepository;

    }


    /**
     * Return page configuration
     *
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function getConfig()
    {
        return $this->_pageConfig;
    }


    /**
     *
     * @param type $code
     * @return \Glugox\PDF\Model\PDFService
     */
    public function setAreaCode($code) {
        $this->_helper->setAreaCode($code);
        return $this;
    }


    /**
     * Returns default input definition for glugox:pdf:create command
     *
     * @return InputDefinition
     */
    public function getCreateCommandDefinition() {
        $definition = new \Symfony\Component\Console\Input\InputDefinition([
            new InputArgument(
                    CreateCommand::SKU_ARGUMENT, InputArgument::OPTIONAL, 'SKU'
            ),
            new InputOption(
                    CreateCommand::OPTION_CREATE_ALL, '-a', InputOption::VALUE_NONE, 'Create PDF using the whole store catalog.'
            ),
            new InputOption(
                    CreateCommand::OPTION_CREATE_CATEGORIES, '-c', InputOption::VALUE_REQUIRED, 'Create pdf of all products in one or more category.'
            ),
            new InputOption(
                CreateCommand::OPTION_FILTER, '-f', InputOption::VALUE_REQUIRED, 'Filter products by attributes.'
            ),
        ]);
        return $definition;
    }

    /**
     * Serves the pdf command
     *
     * @param string $input Command input
     * @return array
     */
    public function serve($strInput = "", InputDefinition $definition = null) {

        if(!$definition){
            $definition = $this->getCreateCommandDefinition();
        }
        $pdfResult = $this->_helper->createPdfResult();
        $this->_input = new StringInput($strInput, $definition);
        return $this->_serve($pdfResult);
    }


    /**
     * Serves the pdf command
     *
     * @param PDFResult $pdfResult
     * @return PDFResult
     * @throws PDFException
     */
    protected function _serve(\Glugox\PDF\Model\PDFResult $pdfResult = null) {

        $this->_helper->info("PDF serve : " . (string) $this->_input);
        $pdfResult = null === $pdfResult ? $this->_helper->createPdfResult() : $pdfResult;

        $sku = (string) $this->_input->getArgument(CreateCommand::SKU_ARGUMENT);

        // check for all command option
        if ($this->_input->getOption(CreateCommand::OPTION_CREATE_ALL)) {
            $pdfResult = $this->createAllProductsPDF($pdfResult);
        }

        // check for category command option
        else if ($category = $this->_input->getOption(CreateCommand::OPTION_CREATE_CATEGORIES)) {
            if (false === \strpos($category, ',')) {
                $pdfResult = $this->createCategoryPDF($category, $pdfResult);
            } else {
                $categories = \explode(',', \trim($category, ","));
                if (\count($categories) <= 1) {
                    throw new \Glugox\PDF\Exception\PDFException(__("Invalid category option value : %1", $category));
                }
                $pdfResult = $this->createCategoriesPDF($categories, $pdfResult);
            }
        }

        // sku
        else if ($sku) {
            if (false === \strpos($sku, ',')) {
                $pdfResult = $this->createProductPDF($sku, $pdfResult);
            } else {
                $skus = \explode(',', \trim($sku, ","));
                if (\count($skus) <= 1) {
                    throw new \Glugox\PDF\Exception\PDFException(__("Invalid sku argument : %1", $sku));
                }
                $pdfResult = $this->createProductsPDF($skus, $pdfResult);
            }
        }

        
        $pdfResult->createFileName( (string) $this->_input);
        return $pdfResult; //getFiltersUsed
    }


    /**
     * Creates pdf for a single product defined by sku
     *
     * @param \string $sku
     * @param PDFResult $pdfResult
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createProductPDF($sku, \Glugox\PDF\Model\PDFResult $pdfResult = null) {

        $this->_helper->info("Creating PDF by SKU : " . $sku);
        $pdfResult = null === $pdfResult ? $this->_helper->createPdfResult() : $pdfResult;

        $pdf = null;
        $product = $this->getProductsProvider()->getProductBySku($sku);
        if (null === $product) {
            $pdfResult->addError(__("Product %1 not found!", $sku));
            $this->_helper->info($this->getProductsProvider()->getError(), true);
        } else {
            $pdfResult = $this->_helper->getPDFProvider()->create([$product], $pdfResult);
        }

        $this->_helper->info("Finished!");
        return $pdfResult;
    }


    /**
     * Creates pdf for a multiple products
     * defined by an array of skus
     *
     * @param \array $skus
     * @param PDFResult $pdfResult
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createProductsPDF(array $skus, \Glugox\PDF\Model\PDFResult $pdfResult = null) {

        $this->_helper->info("Creating PDF by SKUs : " . \implode(",", $skus));
        $pdfResult = null === $pdfResult ? $this->_helper->createPdfResult() : $pdfResult;

        $products = $this->getProductsProvider()->getProductsBySkus($skus);
        $pdf = null;

        /** @var \Magento\Framework\Api\ExtensibleDataInterface **/
        $products = $this->getProductsProvider()->getProductsBySkus($skus);

        if (null === $products) {
            $pdfResult->addError(__("Products %1 not found!", \implode(",", $skus)));
            $this->_helper->info($this->getProductsProvider()->getError(), true);
        } else if(\count($products) > 0 ){

            $this->_helper->info("Fond " . \count($products) . "/".\count($skus)." products.");
            $pdfResult = $this->_helper->getPDFProvider()->create($products, $pdfResult);
        }else{
            $this->_helper->info("No products found from the specified skus. Aborting!");
        }

        $this->_helper->info("Finished!");
        return $pdfResult;
    }


    /**
     * Creates pdf for all products that are in one category
     * defined by category id
     *
     * @param \int $categoryId
     * @param PDFResult $pdfResult
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createCategoryPDF($categoryId, \Glugox\PDF\Model\PDFResult $pdfResult = null) {
        $this->_helper->info("Creating PDF by Category : " . $categoryId);
        $pdfResult = null === $pdfResult ? $this->_helper->createPdfResult() : $pdfResult;

        $pdf = null;

        /** @var \Magento\Framework\Api\ExtensibleDataInterface **/
        $category = $this->categoryRepository->get($categoryId, $this->_helper->getCurrentStoreId());
        if($category){
            $this->getConfig()->setPdfTitle($category->getName());
            $this->getConfig()->setPdfDescription($category->getDescription());
        }

        $products = $this->getProductsProvider()->getProductsByCategories([$categoryId], $pdfResult);

        if (null === $products) {
            $pdfResult->addError(__("Products for category %1 not found!", $categoryId));
            $this->_helper->info($this->getProductsProvider()->getError(), true);
        } else {

            $this->_helper->info("Found " . \count($products) . " products.");
            $pdfResult = $this->_helper->getPDFProvider()->create($products, $pdfResult);
        }

        $this->_helper->info("Finished!");
        return $pdfResult;

    }


    /**
     * Creates pdf for all products that are in multiple categories
     * defined by an array of category ids
     *
     * @param \array $categoryIds
     * @param PDFResult $pdfResult
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createCategoriesPDF(array $categoryIds, \Glugox\PDF\Model\PDFResult $pdfResult = null) {
        $this->_helper->info("Creating PDF by Categories : " . \implode(",", $categoryIds));
        $pdfResult = null === $pdfResult ? $this->_helper->createPdfResult() : $pdfResult;

        $pdf = null;

        /** @var \Magento\Framework\Api\ExtensibleDataInterface **/
        $products = $this->getProductsProvider()->getProductsByCategories($categoryIds, $pdfResult);

        if (null === $products) {
            $pdfResult->addError(__("Products for categories %1 not found!", \implode(",", $categoryIds)));
            $this->_helper->info($this->getProductsProvider()->getError(), true);
        } else {

            $this->_helper->info("Found " . \count($products) . " products.");
            $pdfResult = $this->_helper->getPDFProvider()->create($products, $pdfResult);
        }

        $this->_helper->info("Finished!");
        return $pdfResult;
    }


    /**
     * Creates pdf for all products in a store
     *
     * @param PDFResult $pdfResult
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createAllProductsPDF(\Glugox\PDF\Model\PDFResult $pdfResult = null) {
        /** @var \Magento\Framework\Api\ExtensibleDataInterface **/
        $products = $this->getProductsProvider()->getAllProducts();
        $pdfResult = null === $pdfResult ? $this->_helper->createPdfResult() : $pdfResult;

        if (null === $products) {
            $pdfResult->addError(__("Any products not found!"));
            $this->_helper->info($this->getProductsProvider()->getError(), true);
        } else {

            $this->_helper->info("Found " . \count($products) . " products.");
            $pdfResult = $this->_helper->getPDFProvider()->create($products, $pdfResult);
        }

        $this->_helper->info("Finished!");
        return $pdfResult;
    }


    /**
     * {@inheritdoc}
     */
    public function create(array $pdfData) {
        $pdf = $this->_pdfFactory->create()->setData($pdfData);
        $pdf->save();
        return $pdf;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrCreate(array $pdfData) {

        $pdf = $this->_pdfFactory->create()->getByIndexData($pdfData);
        if (!$pdf->getId()) {
            $pdf = $this->create($pdfData);
        }

        return $pdf;
    }


    /**
     * {@inheritdoc}
     */
    public function update(array $pdfData) {
        $pdf = $this->_loadById($pdfData['pdf_id']);
        //If name has been updated check if it conflicts with an existing integration
        if ($pdf->getName() != $pdfData['name']) {
            $this->_checkPDFByName($pdfData['name']);
        }
        $pdf->addData($pdfData);
        $pdf->save();
        return $pdf;
    }


    /**
     * {@inheritdoc}
     */
    public function delete($pdfId) {
        $pdf = $this->_loadById($pdfId);
        $data = $pdf->getData();
        $pdf->delete();
        return $data;
    }


    /**
     * {@inheritdoc}
     */
    public function get($pdfId) {
        $pdf = $this->_loadById($pdfId);
        return $pdf;
    }


    /**
     * Load PDF by id.
     *
     * @param int $pdfId
     * @return PDFModel
     * @throws \Glugox\PDF\Exception\PDFException
     */
    protected function _loadById($pdfId) {
        $pdf = $this->_pdfFactory->create()->load($pdfId);
        if (!$pdf->getId()) {
            throw new PDFException(__('PDF with ID \'%1\' does not exist.', $pdfId));
        }
        return $pdf;
    }


    /**
     * Check if an pdf exists by the name
     *
     * @param string $name
     * @return void
     * @throws \Glugox\PDF\Exception\PDFException
     */
    private function _checkPDFByName($name) {
        $pdf = $this->_pdfFactory->create()->load($name, 'name');
        if ($pdf->getId()) {
            throw new PDFException(__('PDF with name \'%1\' exists.', $name));
        }
    }


    /**
     * Products provider
     *
     * @return \Glugox\PDF\Model\Provider\Products\ProviderInterface
     */
    public function getProductsProvider(){
        return $this->_helper->getProductsProvider();
    }


    /**
     *
     * @return PDFResult
     */
    public function createPdfResult(){
        return $this->_helper->createPdfResult();
    }

}
