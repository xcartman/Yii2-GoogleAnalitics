<?php
namespace common\tests\unit\components;

use Yii;

class GoogleAnaliticsTest extends \Codeception\Test\Unit
{
    protected $tester;

    public function testMethodsExists()
    {
        $this->assertTrue(method_exists(Yii::$app->ga, 'login'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'getStep'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'request'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'dimension'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'metric'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'segment'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'metricsValues'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'one'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'all'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'getBegin'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'getReporstCount'));
        $this->assertTrue(method_exists(Yii::$app->ga, 'printReports'));
    }

    public function testPropertysExists()
    {
        $this->assertTrue(property_exists(Yii::$app->ga, 'client'));
        $this->assertTrue(property_exists(Yii::$app->ga, 'privateKey'));
        $this->assertTrue(property_exists(Yii::$app->ga, 'viewId'));
        $this->assertTrue(property_exists(Yii::$app->ga, 'dateRanges'));
        $this->assertTrue(property_exists(Yii::$app->ga, 'metrics'));
        $this->assertTrue(property_exists(Yii::$app->ga, 'segments'));
        $this->assertTrue(property_exists(Yii::$app->ga, 'dimensions'));
        $this->assertTrue(property_exists(Yii::$app->ga, 'response'));
        $this->assertTrue(property_exists(Yii::$app->ga, 'request'));
    }

    public function testMethodsWork(){
        $googleAnalitics = clone Yii::$app->ga;

        $this->assertNull(Yii::$app->ga->dateRanges);
        $googleAnalitics->dateRange('7daysAgo', 'today');
        $this->assertInternalType('array', $googleAnalitics->dateRanges);
        $this->assertEquals(1, sizeof($googleAnalitics->dateRanges));

        /**************************************************************/

        $this->assertNull($googleAnalitics->metrics);
        $googleAnalitics->metric('ga:uniqueEvents');
        $this->assertInternalType('array', $googleAnalitics->metrics);
        $this->assertEquals(1, sizeof($googleAnalitics->metrics));

        /***************************************************************/

        $this->assertNull($googleAnalitics->dimensions);
        $googleAnalitics->dimension('ga:segment');
        $this->assertInternalType('array', $googleAnalitics->dimensions);
        $this->assertEquals(1, sizeof($googleAnalitics->dimensions));

        /***************************************************************/

        $this->assertNull($googleAnalitics->segments);
        $googleAnalitics->segment('ga:eventLabel', 'VIEW_DEAL_ROOM');
        $this->assertInternalType('array', $googleAnalitics->segments);
        $this->assertEquals(1, sizeof($googleAnalitics->segments));

        /***************************************************************/

        $this->assertNull($googleAnalitics->response);

        /***************************************************************/


        $this->assertNull($googleAnalitics->request);
        $googleAnalitics->request();
        $this->assertInternalType('object', $googleAnalitics->request);
        $this->assertEquals(1, sizeof($googleAnalitics->request));

        /***************************************************************/
        
        $this->assertInternalType('object', $googleAnalitics->response);

        /***************************************************************/

        $report = $googleAnalitics->one();
        $this->assertInternalType('array', $report);
    }
}
