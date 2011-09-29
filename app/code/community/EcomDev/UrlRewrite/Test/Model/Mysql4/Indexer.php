<?php
/**
 * Alternative Url Rewrite Indexer
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   EcomDev
 * @package    EcomDev_UrlRewrite
 * @copyright  Copyright (c) 2011 EcomDev BV (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan.chepurnyi@ecomdev.org>
 */

/**
 * Url Rewrite Indexer resource model integration test case
 * Very simple one, just checks output that was generated by SQL queries 
 * 
 * @loadSharedFixture data
 * @doNotIndexAll
 */
class EcomDev_UrlRewrite_Test_Model_Mysql4_Indexer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Url rewrite indexer resource model instance
     * 
     * @var EcomDev_UrlRewrite_Model_Mysql4_Indexer
     */
    protected $resourceModel = null;

    /**
     * Initializes model under test and disables events 
     * for checking logic in isolation from custom its customizations
     *  
     * (non-PHPdoc)
     * @see EcomDev_PHPUnit_Test_Case::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        $this->resourceModel = Mage::getResourceModel('ecomdev_urlrewrite/indexer');
        $this->app()->disableEvents();
    }
    
    /**
     * Enables events back
     * (non-PHPdoc)
     * @see EcomDev_PHPUnit_Test_Case::tearDown()
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->app()->enableEvents();
    }
    
    /**
     * Test for generation of category relation index data 
     * 
     * @param string $dataSet for expectation
     * @param int|array $categoryIds
     * @param string $type
     * @param array|null $reindexCategoryIds if null reindex all category relations
     * 
     * @loadFixture clear
     * @dataProvider dataProvider
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::_generateCategoryRelationIndex
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::getCategoryRelations
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::_getRelatedCategoryIdsSelect
     */
    public function testCreationOfCategoryRelations($dataSet, $categoryIds, $type, array $reindexCategoryIds = null)
    {
        // Generate index
        EcomDev_Utils_Reflection::invokeRestrictedMethod(
            $this->resourceModel, 
            '_generateCategoryRelationIndex', 
            array($reindexCategoryIds)
        );
        
        $result = $this->resourceModel->getCategoryRelations($categoryIds, $type);
        $this->assertEquals(
            $this->expected($dataSet)->getResult(), 
            $result
        );
        return $this;
    }
    
    /**
     * Test for generation of category request path index data 
     * 
     * @param string $dataSet for expectation
     * @param int|array $categoryIds
     * @param array|null $reindexCategoryIds if null reindex all data
     * 
     * @loadFixture clear
     * @dataProvider dataProvider
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::_generateCategoryRelationIndex
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::getCategoryRequestPathIndex
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::_getCategoryRequestPathSelect
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::_cleanUrlPath
     */
    public function testCreationOfCategoryRequestPathIndex($dataSet, $categoryIds, array $reindexCategoryIds = null)
    {
        // Generate transliteration
        EcomDev_Utils_Reflection::invokeRestrictedMethod(
            $this->resourceModel, 
            '_generateTransliterateData'
        );
        
        // Generate index
        EcomDev_Utils_Reflection::invokeRestrictedMethod(
            $this->resourceModel, 
            '_generateCategoryRequestPathIndex', 
            array($reindexCategoryIds)
        );
        
        $result = $this->resourceModel->getCategoryRequestPathIndex($categoryIds);
        $this->assertEquals(
            $this->expected($dataSet)->getResult(), 
            $result
        );

        return $this;
    }
    
    /**
     * Test for generation of category request path index data 
     * 
     * If no product and categories ids specified for reindex process, it will rebuild all index
     * If product and category ids specified together, product ids will be ignored
     * 
     * @param string $dataSet for expectation
     * @param int|array $categoryIds
     * @param array|null $reindexCategoryIds 
     * @param array|null $reindexProductIds
     * 
     * @loadFixture clear
     * @loadFixture categoryRelationIndex
     * @loadFixture categoryRequestPathIndex
     * @dataProvider dataProvider
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::_generateProductUrlPathIndex
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::_getProductRequestPathSelect
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::getProductRequestPathIndex
     * @covers EcomDev_UrlRewrite_Model_Mysql4_Indexer::_cleanUrlPath
     */
    public function testCreationOfProductRequestPathIndex($dataSet, $productIds, array $reindexCategoryIds = null, array $reindexProductIds = null)
    {
        // Generate transliteration
        EcomDev_Utils_Reflection::invokeRestrictedMethod(
            $this->resourceModel, 
            '_generateTransliterateData'
        );
        
        // Generate index
        EcomDev_Utils_Reflection::invokeRestrictedMethod(
            $this->resourceModel, 
            '_generateProductRequestPathIndex', 
            array($reindexCategoryIds, $reindexProductIds)
        );
        
        $result = $this->resourceModel->getProductRequestPathIndex($productIds);
        
        $this->assertEquals(
            $this->expected($dataSet)->getResult(), 
            $result
        );
        return $this;
    }
    
    
}
