<?php
/**
 * @link https://github.com/xcartman/Yii2-GoogleAnalitics
 * @copyright Copyright (c) 2018 Chorniy Evgeny
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace xcartman\ga;

use Yii;
use yii\base\Component;
use Google_Client;
use Google_Service_Analytics;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_SegmentDimensionFilter;
use Google_Service_AnalyticsReporting_SegmentFilterClause;
use Google_Service_AnalyticsReporting_OrFiltersForSegment;
use Google_Service_AnalyticsReporting_SimpleSegment;
use Google_Service_AnalyticsReporting_SegmentFilter;
use Google_Service_AnalyticsReporting_SegmentDefinition;
use Google_Service_AnalyticsReporting_DynamicSegment;
use Google_Service_AnalyticsReporting_Segment;

/**
 * Class GoogleAnalitics implements a link between your application and Google Analytics api
 * @link https://developers.google.com/analytics/devguides/reporting/core/v4/quickstart/service-php
 * @link https://ga-dev-tools.appspot.com/account-explorer/
 * @link https://developers.google.com/analytics/devguides/reporting/core/dimsmets
 * quotes limit check it or u will be banned
 * @link https://developers.google.com/analytics/devguides/config/mgmt/v3/limits-quotas
 * 
 * @property Google_Service_AnalyticsReporting|null $client
 * @property string|null $privateKey
 * @property string|null $viewId
 * @property array|null  $dateRanges
 * @property array|null  $metrics
 * @property array|null  $segments
 * @property array|null  $dimensions
 * @property array|null  $response
 * @property object|null $request
 */

class GoogleAnalitics extends Component {
    /**
     * $client
     * @var Google_Service_AnalyticsReporting|null
     */
    public $client = null;
    /**
     * $privateKey 
     * @var string|null
     */
    public $privateKey = null;
    /**
     * $viewId from where get data
     * @link https://ga-dev-tools.appspot.com/account-explorer/
     * @var string|null
     */
    public $viewId = null;

    /**
     * $dateRanges contains array of Google_Service_AnalyticsReporting_DateRange
     * @var array|null
     */
    public $dateRanges = null;
    /**
     * $metrics contains array of Google_Service_AnalyticsReporting_Metric
     * @var array|null
     */
    public $metrics = null; 
    /**
     * $segments contains array of Google_Service_AnalyticsReporting_Segment
     * @var array|null
     */
    public $segments = null;
    /**
     * $dimensions contains array of Google_Service_AnalyticsReporting_Dimension
     * @var array|null
     */
    public $dimensions = null;
    /**
     * $response contains array of Google_Service_AnalyticsReporting_Report
     * @var array|null
     */
    public $response = null;
    /**
     * $request conteins Google_Service_AnalyticsReporting_GetReportsRequest
     * @var object|null
     */
    public $request = null;

    public function __construct($config = []){
        parent::__construct($config);
        self::login();
    }
    /**
     * Clean the object for reuse
     *
     * @return self
     */
    public function getBegin(): self {
        $exclude = ['client','privateKey','viewId'];

        foreach ($this as $key => $value) {
            if (in_array($key, $exclude)) continue;
            $this->{$key} = null;
        }

        return $this;
    }
    /**
     * Returns the number of reports
     * @return int
     */
    public function getReporstCount(): integer {
        return sizeof($this->response) ?? 0;
    }

    /**
     * Count demensions
     * @return int
     */
    public function getStep(): int {
        return sizeof($this->dimensions) ?? 0;
    }
    /**
     * @return void 
     */
    private function login(): void {
        $KEY_FILE_LOCATION = getcwd() . '/'.$this->privateKey;
        $client = new Google_Client();
        $client->setApplicationName("Darcmetter Analytics Reporting");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $this->client = new Google_Service_AnalyticsReporting($client);      
    }
    /**
     * Makes queries to statistics
     * @return self
     */
    public function request(): self {
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId((string)$this->viewId);
        
        if (!empty($this->dateRanges)){
            $request->setDateRanges($this->dateRanges);
        }

        if (!empty($this->segments)){
            $request->setSegments($this->segments);
        }

        if (!empty($this->dimensions)){
            $request->setDimensions($this->dimensions);
        }
        
        if (!empty($this->metrics)){
            $request->setMetrics($this->metrics);
        }

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        
        $this->request = $request;
        $this->response = $this->client->reports->batchGet($body); 
        
        return $this;      
    }
    /**
     * dimension - Adds dimensions to get data from them
     * @link https://developers.google.com/analytics/devguides/reporting/core/dimsmets
     * @param string $expression
     *
     * @return self
     */
    public function dimension(string $expression): self {
        $dimension = new Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName($expression);
        $this->dimensions[] = $dimension;
        
        return $this;
    }
    /**
     * Adds metrics to get data from them
     * @link https://developers.google.com/analytics/devguides/reporting/core/dimsmets
     * @param string $expression 
     * @param string $metricName     
     *
     * @return self
     */
    public function metric(string $expression, string $metricName = null): self {      
        if (empty($metricName)){
            $metricName = $expression;
        }

        $metrics = new Google_Service_AnalyticsReporting_Metric();
        $metrics->setExpression($expression);
        $metrics->setAlias($metricName); 

        $this->metrics[] = $metrics;

        return $this;
    }
    /**
     * Adds a date range to the query 
     * 
     * important default by google is {"startDate": "7daysAgo", "endDate": "yesterday"}.
     *
     * @param string $from 
     * @param string $to  
     *
     * @return self
     */
    public function dateRange(string $from = "7daysAgo", string $to = "today"): self {
        $dateRanges = new Google_Service_AnalyticsReporting_DateRange();
        $dateRanges->setStartDate($from);
        $dateRanges->setEndDate($to);
        $this->dateRanges[] = $dateRanges;

        return $this;
    }

    /**
     * Create simple segment filter 
     * @link https://developers.google.com/analytics/devguides/reporting/core/dimsmets               
     * @param string $dimension                 
     * @param string $dimensionFilterExpression
     * @param string $segmentName
     *
     * @return Google_Service_AnalyticsReporting_Segment 
     */
    function segment(
        string $dimension, 
        string $dimensionFilterExpression,
        string $operator = "EXACT",
        string $segmentName = null
    ): self 
    {

        if (empty($segmentName)){
            $segmentName = $dimensionFilterExpression;
        }
        // Create Dimension Filter.
        $dimensionFilter = new Google_Service_AnalyticsReporting_SegmentDimensionFilter();
        /**
         * @link https://developers.google.com/analytics/devguides/reporting/core/dimsmets 
         */
        $dimensionFilter->setDimensionName($dimension);
       
        /**
         * @link https://developers.google.com/analytics/devguides/reporting/core/v4/rest/v4/reports/batchGet#Operator
         */
        $dimensionFilter->setOperator($operator);

        $dimensionFilter->setExpressions(array($dimensionFilterExpression));

        // Create Segment Filter Clause.
        $segmentFilterClause = new Google_Service_AnalyticsReporting_SegmentFilterClause();
        $segmentFilterClause->setDimensionFilter($dimensionFilter);

        // Create the Or Filters for Segment.
        $orFiltersForSegment = new Google_Service_AnalyticsReporting_OrFiltersForSegment();
        $orFiltersForSegment->setSegmentFilterClauses(array($segmentFilterClause));

        // Create the Simple Segment.
        $simpleSegment = new Google_Service_AnalyticsReporting_SimpleSegment();
        $simpleSegment->setOrFiltersForSegment(array($orFiltersForSegment));

        // Create the Segment Filters.
        $segmentFilter = new Google_Service_AnalyticsReporting_SegmentFilter();
        $segmentFilter->setSimpleSegment($simpleSegment);

        // Create the Segment Definition.
        $segmentDefinition = new Google_Service_AnalyticsReporting_SegmentDefinition();
        $segmentDefinition->setSegmentFilters(array($segmentFilter));

        // Create the Dynamic Segment.
        $dynamicSegment = new Google_Service_AnalyticsReporting_DynamicSegment();
        $dynamicSegment->setSessionSegment($segmentDefinition);
        $dynamicSegment->setName($segmentName);

        // Create the Segments object.
        $segment = new Google_Service_AnalyticsReporting_Segment();
        $segment->setDynamicSegment($dynamicSegment);

        $this->segments[] = $segment;

        return $this;
    }
    /**
     * Gets values ​​from the metric
     *
     * @param array $metrics       
     * @param array $metricHeaders 
     * 
     * @return array
     */
    private function metricsValues(array $metrics, array $metricHeaders): array {
        $values = array();
        for ($i = 0; $i < count($metrics); $i++) {
            $mvalues = $metrics[$i]->getValues();
            for ($j = 0; $j < count($mvalues); $j++) {
                $entry = $metricHeaders[$j];
                $values[$entry->getName()] = $mvalues[$j];
            }   
        }
        return $values;
    }

    /**
     * Gets all reports readings from analytics
     *
     * @return array
     */
    public function all(): array {
        $size = $this->reporstCount;
        $reports = array();
        for($i = 0; $i < $size; $i++){
            $reports[] = $this->getReport($i);
        }
        return $reports;
    }

    /**
     * Converts a response from an analytic to an array
     *
     * @param int|integer $report [get report by Index]
     *
     * @return array
     */
    public function one(int $report = 0): array {
        $values = array();

        $header = $this->response->reports[$report]->getColumnHeader();
        $dimensionHeaders = $header->getDimensions();
        $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
        $rows = $this->response->reports[$report]->getData()->getRows();
        for ($r=0; $r < sizeof($rows); $r++) {
            $d = $rows[$r]->getDimensions();
            $metrics = $rows[$r]->getMetrics();

            $size = sizeof($d);
            $step = $this->getStep();

            for($i=0; $i < $size; $i += $step){
                $target = &$values;

                // max(1, $j) to suppress any action when $step == 1
                for ($j = 0; max(1, $j) < $step; $j++) {
                    $target = &$target[$d[$i + $j]];
                }

                $target = $this->metricsValues(
                    $metrics,
                    $metricHeaders
                );

            }
        }
        return $values;    
    }
    /**
     * Displays a response from the analitics
     */
    public function printReports(): void {
        $reports = $this->response;
        for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
            $report = $reports[ $reportIndex ];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();

            for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $row = $rows[ $rowIndex ];
                $dimensions = $row->getDimensions();
                $metrics = $row->getMetrics();
                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                    print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");
                }

                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        $entry = $metricHeaders[$k];
                        print($entry->getName() . ": " . $values[$k] . "\n");
                    }
                }
            }
        }
    }
}