<?php

namespace ByTIC\Payments\Tests;

use ByTIC\Payments\Models\Methods\PaymentMethods;
use ByTIC\Payments\Models\Purchases\Purchases;
use ByTIC\Payments\Utility\PaymentsModels;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Nip\Records\AbstractModels\RecordManager;
use Nip\Records\Locator\ModelLocator;

/**
 * Class AbstractTestCase
 * @package ByTIC\Payments\Tests
 */
abstract class AbstractTestCase extends AbstractTest
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        ModelLocator::instance()->getModelRegistry()->setItems([]);
        PaymentsModels::reset();
    }

    protected function initUtilityModel($type, $value = null)
    {
        if ($value instanceof RecordManager) {
            ModelLocator::set($type, $value);
        } else {
            $class = $this->generateRepositoryClass($type);
            $value = call_user_func([$class, 'instance']);

            ModelLocator::set($class, $value);
            ModelLocator::set($type, $value);
        }
    }

    protected function generateRepositoryClass($type)
    {
        switch ($type) {
            case 'purchases' :
                return Purchases::class;
            case 'methods' :
                return PaymentMethods::class;
        }
    }
}
