<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    OpenMage_Tests
 */

declare(strict_types=1);

namespace OpenMage\Tests\Unit\Mage\Catalog\Model\Product\Attribute\Backend;

use Mage;
use Mage_Catalog_Model_Product;
use Mage_Catalog_Model_Product_Attribute_Backend_Media as Subject;
use Mage_Catalog_Model_Product_Media_Config;
use Mage_Catalog_Model_Resource_Eav_Attribute;
use OpenMage\Tests\Unit\OpenMageTest;
use OpenMage\Tests\Unit\Traits\DataProvider\Mage\Catalog\Model\Product\Attribute\Backend\MediaTrait;

final class MediaTest extends OpenMageTest
{
    use MediaTrait;

    private string $sourceFile = '';

    private string $tmpMediaFile = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->sourceFile = sys_get_temp_dir() . DS . 'openmage-media-test-' . uniqid() . '.jpg';
        file_put_contents($this->sourceFile, 'jpg');
    }

    protected function tearDown(): void
    {
        foreach ([$this->sourceFile, $this->tmpMediaFile] as $file) {
            if ($file !== '' && is_file($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    /**
     * Adding an image to a product as the admin product form posts it, before beforeSave() decodes the JSON
     *
     * @group Model
     */
    public function testAddImageWithJsonGallery(): void
    {
        $attribute = new Mage_Catalog_Model_Resource_Eav_Attribute();
        $attribute->setAttributeCode('media_gallery');

        $subject = new Subject();
        $subject->setAttribute($attribute);

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');
        $product->setData('media_gallery', [
            'images' => '[{"file":"/e/x/existing.jpg","position":"3","label":"","disabled":"0"}]',
            'values' => '{}',
        ]);

        $fileName = $subject->addImage($product, $this->sourceFile);

        /** @var Mage_Catalog_Model_Product_Media_Config $mediaConfig */
        $mediaConfig = Mage::getSingleton('catalog/product_media_config');
        $this->tmpMediaFile = $mediaConfig->getTmpMediaPath($fileName);

        $images = $product->getData('media_gallery')['images'];
        self::assertCount(2, $images);
        self::assertSame('/e/x/existing.jpg', $images[0]['file']);
        self::assertSame($fileName, $images[1]['file']);
        self::assertSame(4, $images[1]['position']);
    }
}
