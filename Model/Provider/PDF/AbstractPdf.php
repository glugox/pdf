<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Provider\PDF;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Products PDF abstract model
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractPdf extends \Magento\Framework\DataObject {

    /**
     * Y coordinate
     *
     * @var int
     */
    public $y;

    /**
     * Item renderers with render type key
     * model    => the model name
     * renderer => the renderer model
     *
     * @var array
     */
    protected $_renderers = [];

    /**
     * Helper
     *
     * @var \Glugox\PDF\Helper\Data
     */
    protected $_helper;

    /**
     * Zend PDF object
     *
     * @var \Zend_Pdf
     */
    protected $_pdf;

    /**
     * Retrieve PDF
     *
     * @return \Zend_Pdf
     */
    abstract public function getPdf();

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;

    /**
     *
     * @param \Glugox\PDF\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param array $data
     */
    public function __construct(
    \Glugox\PDF\Helper\Data $helper,
            \Magento\Framework\Stdlib\StringUtils $string,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
            array $data = []
    ) {
        $this->_helper = $helper;
        $this->_localeDate = $localeDate;
        $this->string = $string;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);

        parent::__construct($data);

    }


    /**
     * Returns the total width in points of the string using the specified font and
     * size.
     *
     * This is not the most efficient way to perform this calculation. I'm
     * concentrating optimization efforts on the upcoming layout manager class.
     * Similar calculations exist inside the layout manager class, but widths are
     * generally calculated only after determining line fragments.
     *
     * @param  string $string
     * @param  \Zend_Pdf_Resource_Font $font
     * @param  float $fontSize Font size in points
     * @return float
     */
    public function widthForStringUsingFontSize($string, $font, $fontSize) {
        $drawingString = '"libiconv"' == ICONV_IMPL ? iconv(
                        'UTF-8', 'UTF-16BE//IGNORE', $string
                ) : @iconv(
                        'UTF-8', 'UTF-16BE', $string
        );

        $characters = [];
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = ord($drawingString[$i++]) << 8 | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = array_sum($widths) / $font->getUnitsPerEm() * $fontSize;
        return $stringWidth;
    }


    /**
     * Calculate coordinates to draw something in a column aligned to the right
     *
     * @param  string $string
     * @param  int $x
     * @param  int $columnWidth
     * @param  \Zend_Pdf_Resource_Font $font
     * @param  int $fontSize
     * @param  int $padding
     * @return int
     */
    public function getAlignRight($string, $x, $columnWidth,
            \Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5) {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + $columnWidth - $width - $padding;
    }


    /**
     * Calculate coordinates to draw something in a column aligned to the center
     *
     * @param  string $string
     * @param  int $x
     * @param  int $columnWidth
     * @param  \Zend_Pdf_Resource_Font $font
     * @param  int $fontSize
     * @return int
     */
    public function getAlignCenter($string, $x, $columnWidth,
            \Zend_Pdf_Resource_Font $font, $fontSize) {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + round(($columnWidth - $width) / 2);
    }



    /**
     * Insert title and number for concrete document type
     *
     * @param  \Zend_Pdf_Page $page
     * @param  string $text
     * @return void
     */
    public function insertDocumentNumber(\Zend_Pdf_Page $page, $text) {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 35, $docHeader[1] - 15, 'UTF-8');
    }



    /**
     * Parse item description
     *
     * @param  \Magento\Framework\DataObject $item
     * @return array
     */
    protected function _parseItemDescription($item) {
        $matches = [];
        $description = $item->getDescription();
        if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
            return $matches[1];
        }

        return [$description];
    }


    /**
     * Before getPdf processing
     *
     * @return void
     */
    protected function _beforeGetPdf() {
        //
    }


    /**
     * After getPdf processing
     *
     * @return void
     */
    protected function _afterGetPdf() {
        //
    }


    /**
     * Initialize renderer process
     *
     * @param string $type
     * @return void
     */
    protected function _initRenderer($type) {

        $this->_helper->info("Initializing renderer: " . $type);

        switch ($type) {
            case 'product' :
                $model = '\Glugox\PDF\Model\Renderer\Product';
                break;
            case 'products':
                $model = '\Glugox\PDF\Model\Renderer\Products';
                break;
            default:
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid renderer type.'));
        }

        $this->_renderers[$type] = ['model' => $model, 'renderer' => $this->_helper->getInstance($model)];
    }


    /**
     * Retrieve renderer model
     *
     * @param  string $type
     * @return \Glugox\PDF\Model\Renderer\AbstractItems
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getRenderer($type) {

        if (!isset($this->_renderers[$type])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid renderer type.'));
        }

        return $this->_renderers[$type]['renderer'];
    }


    /**
     * Public method of protected @see _getRenderer()
     *
     * Retrieve renderer model
     *
     * @param  string $type
     */
    public function getRenderer($type) {
        return $this->_getRenderer($type);
    }


    /**
     * Draw Product process
     *
     * @param  \Zend_Pdf_Page $page
     * @param  \array $products
     * @return \Zend_Pdf_Page
     */
    protected function _drawProduct(\Zend_Pdf_Page $page, \Magento\Catalog\Model\Product $product) {
        $renderer = $this->_getRenderer('product');
        $renderer->setProduct($product);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setRenderedModel($this);

        $renderer->draw();

        return $renderer->getPage();
    }


    /**
     * Draw Product process
     *
     * @param  \Zend_Pdf_Page $page
     * @param  $products
     * @return \Zend_Pdf_Page
     */
    protected function _drawProducts(\Zend_Pdf_Page $page, $products) {
        $renderer = $this->_getRenderer('products');
        $renderer->setProducts($products);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setRenderedModel($this);

        $renderer->draw();

        return $renderer->getPage();
    }





    /**
     * Set font as regular
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7) {
        $font = \Zend_Pdf_Font::fontWithPath(
                        $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }


    /**
     * Set font as bold
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7) {
        $font = \Zend_Pdf_Font::fontWithPath(
                        $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }


    /**
     * Set font as italic
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7) {
        $font = \Zend_Pdf_Font::fontWithPath(
                        $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }


    /**
     * Set PDF object
     *
     * @param  \Zend_Pdf $pdf
     * @return $this
     */
    protected function _setPdf(\Zend_Pdf $pdf) {
        $this->_pdf = $pdf;
        return $this;
    }


    /**
     * Retrieve PDF object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Zend_Pdf
     */
    protected function _getPdf() {
        if (!$this->_pdf instanceof \Zend_Pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define the PDF object before using.'));
        }

        return $this->_pdf;
    }


    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = []) {
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : \Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        return $page;
    }


    /**
     * Draw lines
     *
     * Draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parametr), optional left, right
     * height       int;line spacing (default 10)
     *
     * @param  \Zend_Pdf_Page $page
     * @param  array $draw
     * @param  array $pageSettings
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Zend_Pdf_Page
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function drawLineBlocks(\Zend_Pdf_Page $page, array $draw,
            array $pageSettings = []) {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                __('We don\'t recognize the draw line data. Please define the "lines" array.')
                );
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = [$column['text']];
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = \Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = [$column['text']];
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        if ($this->y - $lineSpacing < 15) {
                            $page = $this->newPage($pageSettings);
                        }

                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                } else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                            default:
                                break;
                        }
                        $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }


}
