<?php

/*
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Glugox\PDF\Model\Provider;
/**
 * Description of DefaultPDFProvider
 *
 * @author Eko
 */
class DefaultPDFProvider extends PDF {

    /**
     * Creates the PDF
     */
    public function create($products, \Glugox\PDF\Model\PDFResult $pdfResult = null) {
        return parent::create($products, $pdfResult);
    }
}
