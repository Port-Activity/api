<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class SeaChartMarkerTypeModelTest extends TestCase
{
    public function testSet(): void
    {
        $model = new SeaChartMarkerTypeModel();
        $model->set("marker-type-name");
        $this->assertEquals($model->name, "marker-type-name");
    }
}
