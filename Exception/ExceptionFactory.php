<?php
/**
 * ExceptionFactory.php
 *
 * Factory for creating exceptions, which requires
 * the ObjectManager for instance generation.
 *
 * @link https://devdocs.magento.com/guides/v2.3/extension-dev-guide/factories.html
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Exception;

use AuroraExtensions\SimpleReturns\Shared\ModuleComponentInterface;
use Magento\Framework\ObjectManagerInterface;

class ExceptionFactory implements ModuleComponentInterface
{
    /** @property ObjectManagerInterface $objectManager */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create exception from type given.
     *
     * @param string $type
     * @return mixed
     * @throws Exception
     */
    public function create(string $type)
    {
        /** @var string $baseType */
        $baseType = \Exception::class;

        if ($type !== $baseType && !is_subclass_of($type, $baseType)) {
            throw new \Exception(
                __(
                    self::ERROR_INVALID_EXCEPTION_TYPE,
                    $type
                )
            );
        }

        return $this->objectManager->create($type);
    }
}