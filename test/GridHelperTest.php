<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2019 Serhii Popov
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Popov
 * @package Popov_ZfcDataGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace PopovTest\ZfcDataGrid;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use PopovTest\ZfcDataGrid\Bootstrap;
use Popov\ZfcDataGrid\GridHelper;

class GridHelperTest extends TestCase
{
    public function testConvertJqGridToZendFormFormat()
    {
        $postGridData = [
            'product_name' => 'Test Product Name',
            'product_price' => '99.99',
            'attribute_id' => '10',
            'attribute_code' => 'Test Attr Code',
            'attribute_name' => 'Test Attr Name',
            'oper' => 'edit',
            'id' => '2',
        ];

        $expectedData = [
            'product' => [
                2 => [
                    'id' => '2',
                    'name' => 'Test Product Name',
                    'price' => '99.99',
                    'attribute' => [
                        'id' => '10',
                        'code' => 'Test Attr Code',
                        'name' => 'Test Attr Name',
                    ],
                ]
            ]
        ];

        /** @var GridHelper|MockInterface $gridHelperMock */
        $gridHelperMock = Mockery::mock(GridHelper::class)->makePartial();
        $gridHelperMock->shouldReceive('getCurrentGridId')->andReturn('product');

        $gridData = $gridHelperMock->prepareExchangeData($postGridData);

        $this->assertEquals($expectedData, $gridData);
    }
}
