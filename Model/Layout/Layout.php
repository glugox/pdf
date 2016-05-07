<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Layout;


use Glugox\PDF\Exception\PDFException;
use Glugox\PDF\Model\Page\Config;
use Glugox\PDF\Model\Renderer\RendererFactory;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\TestFramework\Inspection\Exception;


class Layout extends \Magento\Framework\Simplexml\Config implements LayoutInterface
{


    /**
     * Supported types
     */
    const TYPE_CONTAINER = 'container';
    const TYPE_BLOCK = 'block';
    const KEY_ATTRIBUTES = 'attributes';

    const SUPPORTED_TYPES = [ self::TYPE_CONTAINER, self::TYPE_BLOCK ];

    const ATTRIBUTE_KEY_NAME       = 'name';
    const ATTRIBUTE_KEY_RENDERER   = 'renderer';
    const ATTRIBUTE_KEY_ORDER      = 'order';
    const ATTRIBUTE_KEY_STYLE      = 'style';
    const ATTRIBUTE_KEY_SRC        = 'src';
    const ATTRIBUTE_KEY_PARENT     = 'parent'; // parent can not be set in xml (not in self::DEFAULT_ATTRIBUTES), but it is processed by reading parent node.
    const ATTRIBUTE_KEY_TYPE       = 'type'; // type can not be set in xml attributed, it is xml node name, (not in self::DEFAULT_ATTRIBUTES), but it is processed by reading parent node.


    const DEFAULT_ATTRIBUTES = [self::ATTRIBUTE_KEY_NAME, self::ATTRIBUTE_KEY_RENDERER, self::ATTRIBUTE_KEY_ORDER, self::ATTRIBUTE_KEY_STYLE, self::ATTRIBUTE_KEY_SRC];


    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected $_readFactory;


    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    private $_fileSource;

    /**
     * Cumulative array of update XML strings
     *
     * @var array
     */
    protected $updates = [];

    /**
     * Layout structure model
     *
     * @var \Glugox\PDF\Model\Layout\ScheduledStructure
     */
    protected $_scheduledStructure;


    /**
     * @var namespace \Glugox\PDF\Model\Layout\Data\Structure
     */
    protected $_structure;


    /**
     * @var \Glugox\PDF\Model\Page\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    private $theme;

    /**
     * @var string
     */
    protected $pageLayout = null;


    /**
     * @var \Glugox\PDF\Model\Renderer\RootRenderer
     */
    protected $_rootRenderer = null;

    /**
     * @var boolean Did we passed all children tree to render into root renderer
     */
    protected $_createdRootRenderer = false;


    /**
     * @var bool
     */
    protected $isBuilt = false;

    /**
     * @var \Glugox\PDF\Model\Renderer\RendererFactory
     */
    protected $_rendererFactory = false;


    /**
     * Layout constructor.
     * @param \Magento\Framework\Filesystem\DriverInterface $driver
     */
    public function __construct(
        ReadFactory $readFactory,
        \Glugox\PDF\Model\Layout\ScheduledStructure $schelduledStructure,
        \Glugox\PDF\Model\Layout\Data\Structure $structure,
        \Magento\Framework\View\File\CollectorInterface $fileSource,
        \Magento\Framework\View\DesignInterface $design,
        \Glugox\PDF\Model\Renderer\RootRenderer $rootRenderer,
        \Glugox\PDF\Model\Renderer\RendererFactory $rendererFactory,
        \Magento\Framework\View\Design\ThemeInterface $theme = null
    )
    {
        $this->_readFactory = $readFactory;
        $this->_scheduledStructure = $schelduledStructure;
        $this->_structure = $structure;
        $this->_fileSource = $fileSource;
        $this->theme = $theme ?: $design->getDesignTheme();
        $this->_rootRenderer = $rootRenderer;
        $this->_rendererFactory = $rendererFactory;
    }


    /**
     * Build page config
     * @return void
     */
    public function build()
    {
        if (!$this->isBuilt) {
            $this->isBuilt = true;

            $this->load();
            $this->readElements();
            $this->generateElements();
            $this->processConfigStyling();
        }
    }


    /**
     * Add XML update instruction
     *
     * @param string $update
     * @return $this
     */
    public function addUpdate($update)
    {
        $this->updates[] = $update;
        return $this;
    }


    /**
     * @return ScheduledStructure
     */
    public function getStructure(){
        return $this->_structure;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }


    


    /**
     * Load layout
     */
    public function load(){
        $result = $this->_loadFileLayoutUpdatesXml();
        return $this;
    }

    /**
     *
     */
    protected function _loadFileLayoutUpdatesXml(){

        $layoutStr = '';

        $theme = $this->_getPhysicalTheme($this->theme);
        $filesPattern = '*_pdf.xml';

        switch ($this->getConfig()->getPdfType()){
            case Config::PDF_TYPE_PRODUCT:
                $filesPattern = 'catalog_product_pdf.xml';
                break;
            case Config::PDF_TYPE_LIST:
                $filesPattern = 'catalog_list_pdf.xml';
                break;
            default:
                //
        }

        $updateFiles = $this->_fileSource->getFiles($theme, $filesPattern);
        foreach ($updateFiles as $file) {

            $fileReader = $this->_readFactory->create($file->getFilename(), DriverPool::FILE);
            $fileStr = $fileReader->readAll($file->getName());
            $layoutXml = $this->_loadXmlString($fileStr);

            $fileStr = '<layout>' . $layoutXml->innerXml() . '</layout>';
            $layoutStr .= $fileStr;
        }

        $layoutStr = '<layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $layoutStr . '</layouts>';
        $layoutXml = $this->_loadXmlString($layoutStr);

        $this->setXml($layoutXml);
        $this->addUpdate($layoutXml);

        return $layoutXml;
    }



    /**
     * Read structure of elements from the loaded XML configuration
     *
     * @return void
     */
    public function readElements(){
        $this->readElement($this->getNode());
    }


    /**
     * @param Element $element
     */
    public function readElement( \Glugox\PDF\Model\Layout\Element $element ){

        $nodeName = $element->getName();

        // make sure we read only supported types
        if( \in_array($nodeName, self::SUPPORTED_TYPES) ){

            // read element's default data: name, parent, etc
            $data = $this->readElementDefaults( $element, $element->getParent() );


            // read element's specific data
            switch ($element->getName()) {
                case self::TYPE_CONTAINER :
                    $data = $this->readContainer( $element, $element->getParent(), $data );
                    break;
                case self::TYPE_BLOCK :
                    $data = $this->readBlock( $element, $element->getParent(), $data );
                    break;
            }


            $name = $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_NAME];
            $this->_scheduledStructure->setStructureElementData($name, $data);
        }




        /**
         * We are not reading unsupported element type, but we will read
         * its children, so for example if <body> is not supported, we ll
         * be sure to read the <container name="root" /> inside <body>
         */
        $this->readRecursively($element);
    }


    /**
     * @param Element $currentNode
     * @param Element $parentNode
     * @return array|null
     */
    public function readElementDefaults( \Glugox\PDF\Model\Layout\Element $currentNode, \Glugox\PDF\Model\Layout\Element $parentNode ){


        $name = (string)$currentNode->getAttribute('name');

        if( !$name ){
            $name = $currentNode->getName() . '_' . \uniqid();
        }
        $path = $name;

        // we wont allow adding elements with same elements,
        // those need to be unique
        if( $this->_scheduledStructure->hasStructureElement($name) ){
            throw new PDFException(__("Element with name '%1' already exists!", $name));
        }

        $parentName = $parentNode->getElementName();
        if ($parentName) {
            if ($this->_scheduledStructure->hasPath($parentName)) {
                $path = $this->_scheduledStructure->getPath($parentName) . '/' . $path;
            }
        }
        $this->_scheduledStructure->setPathElement($name, $path);
        $this->_scheduledStructure->setStructureElement($name, [
            'name' => $name,
            'parent' => $parentName
        ]);

        $data = $this->_scheduledStructure->getStructureElementData($name, []);
        foreach (self::DEFAULT_ATTRIBUTES as $attributeKey) {
            $data[self::KEY_ATTRIBUTES][$attributeKey] = (string) $currentNode->getAttribute($attributeKey);
        }
        $data[self::KEY_ATTRIBUTES]['name'] = $name;
        $data[self::KEY_ATTRIBUTES]['parent'] = $parentName;

        return $data;
    }


    /**
     * Read specific data for container elements, the default element's data
     * is being read in readElementDefaults
     *
     * @param Element $currentNode
     * @param Element $parentNode
     * @return array
     */
    public function readContainer( \Glugox\PDF\Model\Layout\Element $currentNode, \Glugox\PDF\Model\Layout\Element $parentNode, $data ){

        $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_TYPE] = self::TYPE_CONTAINER;
        return $data;
    }

    /**
     * Read specific data for block elements, the default element's data
     * is being read in readElementDefaults
     *
     * @param Element $currentNode
     * @param Element $parentNode
     * @return array
     */
    public function readBlock( \Glugox\PDF\Model\Layout\Element $currentNode, \Glugox\PDF\Model\Layout\Element $parentNode, $data ){
        $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_TYPE] = self::TYPE_BLOCK;
        return $data;
    }





    /**
     * @param Element $node
     */
    public function readRecursively( \Glugox\PDF\Model\Layout\Element $element ){
        foreach ($element as $node) {
            $this->readElement($node);
        }
    }



    /**
     * Generate structure of elements from the loaded XML configuration
     *
     * @return void
     */
    public function generateElements(){

        foreach ($this->_scheduledStructure->getStructureElementData() as $schelduledElementData) {
            $this->generateElement( $schelduledElementData );
        }
    }


    /**
     * Generate each element
     *
     * @return void
     */
    public function generateElement($schelduledElementData){


        $name   = isset( $schelduledElementData[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_NAME] )   ? $schelduledElementData[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_NAME]   : null;
        $parent = isset( $schelduledElementData[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_PARENT] ) ? $schelduledElementData[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_PARENT] : null;
        $type   = isset( $schelduledElementData[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_TYPE] )   ? $schelduledElementData[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_TYPE]   : null;


        if(!$name){
            throw new PDFException(__("Element schelduled for generation does not have name!"));
        }
        if(!$type){
            throw new PDFException(__("Element schelduled for generation does not have type!"));
        }

        // check /set generated flag
        if( $this->_scheduledStructure->isElementGenerated($name) ){
            return false;
        }
        $this->_scheduledStructure->setElementAsGenerated($name);

        if( $parent ){
            // first generate parent
            if ($this->_scheduledStructure->hasStructureElementData($parent)) {
                $this->generateElement($this->_scheduledStructure->getStructureElementData($parent));
            }else{
                throw new PDFException(__("Parent '%1' does not exist, requested element for generation: '%2'", $parent, $name ));
            }
        }

        $this->_structure->createElement($name, [self::ATTRIBUTE_KEY_NAME => $name, self::ATTRIBUTE_KEY_TYPE => $type]);
        if( $parent ){
            try {
                $this->_structure->setAsChild($name, $parent);
            } catch (\Exception $e) {
                throw new PDFException(__("Element '%1' can not be set as child to '%2'! Original error: " . $e->getMessage(), $name, $parent));
            }
        }


    }


    /**
     * Element styles can be set in the xml files, but if the user
     * has set some styling in the admin config , than we will override
     * the styles with the config values.
     */
    protected function processConfigStyling(){
        $config = $this->getConfig();
    }


    /**
     * Create renderers using parent-child relations and
     * add them as tree into root renderer.
     *
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getRootRenderer(){

        $this->build();

        if( !$this->_createdRootRenderer ){

            $this->_createdRootRenderer = true;

            $rootElementData = $this->_scheduledStructure->getStructureElementData('root');
            if(empty($rootElementData)){
                throw new PDFException(__("No root data defined for the layout!"));
            }

            $this->_rootRenderer->setName("root");
            $this->processChildRenderersFor($this->_rootRenderer);

        }

        return $this->_rootRenderer;
    }


    /**
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $renderer
     */
    public function processChildRenderersFor(\Glugox\PDF\Model\Renderer\RendererInterface $renderer){

        foreach ($this->_structure->getChildren($renderer->getName()) as $childName => $child) {

            /** @var \Glugox\PDF\Model\Renderer\Container\RendererInterface $renderer */
            $childRenderer = $this->createRenderFor( $childName );
            $this->processChildRenderersFor($childRenderer);
            $renderer->addChild( $childRenderer );
        }

    }


    /**
     * Create renderers for an element by its data from layout xml.
     *
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function createRenderFor( $childName ){

        $data = $this->_scheduledStructure->getStructureElementData($childName);

        $rendererClass  = isset( $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_RENDERER] ) ? $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_RENDERER]    : null;
        $order          = isset( $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_ORDER] )    ? (int) $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_ORDER] : 0;
        $type           = isset( $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_TYPE] )     ? $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_TYPE]        : null;
        $style          = isset( $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_STYLE] )    ? $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_STYLE]       : null;
        $src            = isset( $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_SRC] )      ? $data[self::KEY_ATTRIBUTES][self::ATTRIBUTE_KEY_SRC]         : null;

        $defaultArguments = [self::ATTRIBUTE_KEY_TYPE=> $type, self::ATTRIBUTE_KEY_ORDER => $order, self::ATTRIBUTE_KEY_STYLE => $style, self::ATTRIBUTE_KEY_SRC => $src ];

        switch ($type){
            case self::TYPE_CONTAINER:
                $rendererInstance = $this->_rendererFactory->createContainerRenderer($rendererClass, $defaultArguments);
                break;
            case self::TYPE_BLOCK:
                $rendererInstance = $this->_rendererFactory->createBlockRenderer($rendererClass, $defaultArguments);
                break;

            default:
                throw new PDFException(__("No type found for %1", $childName));
        }

        $rendererInstance->setName($childName);

        return $rendererInstance;

    }


    /**
     * Find the closest physical theme among ancestors and a theme itself
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return \Magento\Theme\Model\Theme
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getPhysicalTheme(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $result = $theme;
        while ($result->getId() && !$result->isPhysical()) {
            $result = $result->getParentTheme();
        }
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(
                    'Unable to find a physical ancestor for a theme \'%1\'.',
                    [$theme->getThemeTitle()]
                )
            );
        }
        return $result;
    }


    /**
    * Return object representation of XML string
    *
    * @param string $xmlString
    * @return \SimpleXMLElement
    */
    protected function _loadXmlString($xmlString)
    {
        return simplexml_load_string($xmlString, 'Glugox\PDF\Model\Layout\Element');
    }
}