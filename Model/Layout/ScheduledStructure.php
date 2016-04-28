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


class ScheduledStructure
{

    /**
     * Information about structural elements, scheduled for creation
     *
     * @var array
     */
    protected $scheduledStructure = [];

    /**
     * Scheduled structure data
     *
     * @var array
     */
    protected $scheduledData = [];

    /**
     * Full information about elements to be populated in the layout structure after generating structure
     *
     * @var array
     */
    protected $scheduledElements = [];


    /**
     * Keep info of generated elements so we do not repeat them if the loop processing parents
     *
     * @var array
     */
    protected $generatedElements = [];

    /**
     * Scheduled structure elements with ifconfig attribute
     *
     * @var array
     */
    protected $scheduledIfconfig = [];

    /**
     * Materialized paths for overlapping workaround of scheduled structural elements
     *
     * @var array
     */
    protected $scheduledPaths = [];


    /**
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(array $data = [])
    {
        $this->scheduledStructure = isset($data['scheduledStructure']) ? $data['scheduledStructure'] : [];
        $this->scheduledData = isset($data['scheduledData']) ? $data['scheduledData'] : [];
        $this->scheduledElements = isset($data['scheduledElements']) ? $data['scheduledElements'] : [];
        $this->scheduledIfconfig = isset($data['scheduledIfconfig']) ? $data['scheduledIfconfig'] : [];
        $this->scheduledPaths = isset($data['scheduledPaths']) ? $data['scheduledPaths'] : [];
    }

    /**
     * Get elements to check ifconfig attribute
     *
     * @return array
     */
    public function getIfconfigList()
    {
        return array_keys(array_intersect_key($this->scheduledElements, $this->scheduledIfconfig));
    }

    /**
     * Get scheduled elements list
     *
     * @return array
     */
    public function getElements()
    {
        return $this->scheduledElements;
    }

    /**
     * Get element by name
     *
     * @param string $elementName
     * @param array $default
     * @return bool|array
     */
    public function getElement($elementName, $default = [])
    {
        return $this->hasElement($elementName) ? $this->scheduledElements[$elementName] : $default;
    }

    /**
     * Check if scheduled elements list is empty
     *
     * @return bool
     */
    public function isElementsEmpty()
    {
        return empty($this->scheduledElements);
    }

    /**
     * Add element to scheduled elements list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     */
    public function setElement($elementName, array $data)
    {
        $this->scheduledElements[$elementName] = $data;
    }

    /**
     * Check if element present in scheduled elements list
     *
     * @param string $elementName
     * @return bool
     */
    public function hasElement($elementName)
    {
        return isset($this->scheduledElements[$elementName]);
    }

    /**
     * Unset specified element from scheduled elements list
     *
     * @param string $elementName
     * @return void
     */
    public function unsetElement($elementName)
    {
        unset($this->scheduledElements[$elementName]);
    }


    /**
     * Get element to check by name
     *
     * @param string $elementName
     * @param mixed $default
     * @return mixed
     */
    public function getIfconfigElement($elementName, $default = null)
    {
        return isset($this->scheduledIfconfig[$elementName]) ? $this->scheduledIfconfig[$elementName] : $default;
    }


    /**
     * Unset element by name removed by ifconfig attribute
     *
     * @param string $elementName
     * @return void
     */
    public function unsetElementFromIfconfigList($elementName)
    {
        unset($this->scheduledIfconfig[$elementName]);
    }

    /**
     * Set element value to check ifconfig attribute
     *
     * @param string $elementName
     * @param string $configPath
     * @param string $scopeType
     * @return void
     */
    public function setElementToIfconfigList($elementName, $configPath, $scopeType)
    {
        $this->scheduledIfconfig[$elementName] = [$configPath, $scopeType];
    }

    /**
     * Get scheduled structure
     *
     * @return array
     */
    public function getStructure()
    {
        return $this->scheduledStructure;
    }

    /**
     * Get element of scheduled structure
     *
     * @param string $elementName
     * @param mixed|null $default
     * @return mixed
     */
    public function getStructureElement($elementName, $default = null)
    {
        return $this->hasStructureElement($elementName) ? $this->scheduledStructure[$elementName] : $default;
    }

    /**
     * Check if scheduled structure is empty
     *
     * @return bool
     */
    public function isStructureEmpty()
    {
        return empty($this->scheduledStructure);
    }

    /**
     * Check if element present in scheduled structure elements list
     *
     * @param string $elementName
     * @return bool
     */
    public function hasStructureElement($elementName)
    {
        return isset($this->scheduledStructure[$elementName]);
    }

    /**
     * Add element to scheduled structure elements list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     */
    public function setStructureElement($elementName, array $data)
    {
        $this->scheduledStructure[$elementName] = $data;
    }

    /**
     * Unset scheduled structure element by name
     *
     * @param string $elementName
     * @return void
     */
    public function unsetStructureElement($elementName)
    {
        unset($this->scheduledStructure[$elementName]);
        unset($this->scheduledData[$elementName]);
    }

    /**
     * Get scheduled data for element or all data
     *
     * @param string $elementName
     * @param null $default
     * @return null
     */
    public function getStructureElementData($elementName = null, $default = null)
    {
        if( !$elementName ){
            return $this->scheduledData;
        }
        return isset($this->scheduledData[$elementName]) ? $this->scheduledData[$elementName] : $default;
    }

    /**
     * Set scheduled data for element
     *
     * @param string $elementName
     * @param array $data
     * @return void
     */
    public function setStructureElementData($elementName, array $data)
    {
        $this->scheduledData[$elementName] = $data;
    }

    /**
     * Check if element present in scheduled structure element data
     *
     * @param string $elementName
     * @return bool
     */
    public function hasStructureElementData($elementName)
    {
        return isset($this->scheduledData[$elementName]);
    }

    /**
     * Get scheduled paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->scheduledPaths;
    }

    /**
     * Get path from paths list
     *
     * @param string $elementName
     * @param mixed $default
     * @return mixed
     */
    public function getPath($elementName, $default = null)
    {
        return $this->hasPath($elementName) ? $this->scheduledPaths[$elementName] : $default;
    }

    /**
     * Check if element present in scheduled paths list
     *
     * @param string $elementName
     * @return bool
     */
    public function hasPath($elementName)
    {
        return isset($this->scheduledPaths[$elementName]);
    }

    /**
     * Add element to scheduled paths elements list
     *
     * @param string $elementName
     * @param string $data
     * @return void
     */
    public function setPathElement($elementName, $data)
    {
        $this->scheduledPaths[$elementName] = $data;
    }

    /**
     * Unset scheduled paths element by name
     *
     * @param string $elementName
     * @return void
     */
    public function unsetPathElement($elementName)
    {
        unset($this->scheduledPaths[$elementName]);
    }


    /**
     * @param $elementName
     */
    public function setElementAsGenerated($elementName){
        $this->generatedElements[] = $elementName;
    }

    /**
     * @return array
     */
    public function getGeneratedElements(){
        return $this->generatedElements;
    }


    /**
     * Checks if the element is generated or not.
     *
     * @param $elementName
     * @return bool
     */
    public function isElementGenerated($elementName){
        return \in_array($elementName, $this->generatedElements);
    }

    /**
     * Flush scheduled paths list
     *
     * @return void
     */
    public function flushPaths()
    {
        $this->scheduledPaths = [];
    }

    /**
     * Flush scheduled structure list
     *
     * @return void
     */
    public function flushScheduledStructure()
    {
        $this->flushPaths();
        $this->scheduledElements = [];
        $this->scheduledStructure = [];
    }


}