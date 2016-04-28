<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Page;


use Glugox\PDF\Exception\PDFException;

class Result
{

    /**
     * @var string
     */
    protected $pageLayout;

    /**
     * @var \Glugox\PDF\Model\Page\Config
     */
    protected $pageConfig;


    /**
     * @var \Zend_Pdf
     */
    protected $pdf = null;

    /**
     * @return \Zend_Pdf
     */
    public function getPdf()
    {
        if(null === $this->pdf){
            throw new PDFException(__("Requesting pdf from result page, but not generated/set yet!"));
        }
        return $this->pdf;
    }

    /**
     * @param \Zend_Pdf $pdf
     */
    public function setPdf($pdf)
    {
        $this->pdf = $pdf;
    }


    /**
     * Constructor
     */
    public function __construct(\Glugox\PDF\Model\Page\Context $context){
        $this->pageConfig = $context->getPageConfig();
    }

    /**
     * Return page configuration
     *
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function getConfig()
    {
        return $this->pageConfig;
    }



}