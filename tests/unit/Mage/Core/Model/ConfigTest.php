<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    OpenMage_Tests
 */

declare(strict_types=1);

namespace OpenMage\Tests\Unit\Mage\Core\Model;

use Override;
use Mage;
use Mage_Core_Model_Config as Subject;
use OpenMage\Tests\Unit\OpenMageTest;
use OpenMage\Tests\Unit\Traits\DataProvider\Mage\Core\Model\ConfigTrait;

final class ConfigTest extends OpenMageTest
{
    use ConfigTrait;

    private static Subject $subject;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$subject = Mage::getModel('core/config');
    }

    /**
     * @group Model
     */
    public function testSaveDeleteGetConfig(): void
    {
        $path = 'test/config';
        $value = 'foo';

        self::assertFalse(self::$subject->getConfig($path));

        self::$subject->saveConfig($path, $value);
        self::assertSame($value, self::$subject->getConfig($path));

        self::$subject->deleteConfig($path);
        self::assertFalse(self::$subject->getConfig($path));
    }

    /**
     * A class alias read from a missing config node is null; that must not raise a deprecation notice
     *
     * @group Model
     */
    public function testGetModelClassNameWithNull(): void
    {
        $deprecations = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$deprecations): bool {
            $deprecations[] = $errstr;
            return true;
        }, E_DEPRECATED);

        try {
            $className = self::$subject->getModelClassName(null);
        } finally {
            restore_error_handler();
        }

        self::assertSame('', $className);
        self::assertSame([], $deprecations);
    }
}
