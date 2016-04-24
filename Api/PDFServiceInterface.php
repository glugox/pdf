<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Api;

use Glugox\PDF\Model\PDF as PDFModel;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * PDF Service Interface
 *
 * @api
 */
interface PDFServiceInterface {



    /**
     * Serves the pdf command
     *
     * @param string $input Command input
     * @return array
     */
    public function serve($strInput = "", InputDefinition $definition = null);


    /**
     * Returns default input definition for glugox:pdf:create command
     *
     * @return InputDefinition
     */
    public function getCreateCommandDefinition();

    /**
     * Creates pdf for a single product defined by sku
     *
     * @param \string $sku
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createProductPDF( $sku );

    /**
     * Creates pdf for a multiple products
     * defined by an array of skus
     *
     * @param \array $skus
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createProductsPDF( array $skus );

    /**
     * Creates pdf for all products that are in one category
     * defined by category id
     *
     * @param \int $categoryId
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createCategoryPDF( $categoryId );

    /**
     * Creates pdf for all products that are in multiple categories
     * defined by an array of category ids
     *
     * @param \array $categoryIds
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createCategoriesPDF( array $categoryIds );

    /**
     * Creates pdf for all products in a store
     * @return \Glugox\PDF\Model\PDFResult
     */
    public function createAllProductsPDF();

    /**
     * Create a new PDF
     *
     * @param array $pdfData
     * @return PDFModel
     */
    public function create(array $pdfData);

    /**
     * Create a new PDF if not exists by data passed.
     * If it does exist, retreive that one
     *
     * @param array $pdfData
     * @return PDFModel
     */
    public function getOrCreate(array $pdfData);

    /**
     * Delete a PDF.
     *
     * @param int $pdfId
     * @return array PDF data
     */
    public function delete($pdfId);
}
