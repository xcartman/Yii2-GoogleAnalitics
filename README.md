# Yii2 GoogleAnalitics

This extension provides [google-analitics](https://github.com/xcartman/Yii2-GoogleAnalitics) integration for the Yii framework.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require xcartman/yii2-google-analitics
```

or add

```
"xcartman/yii2-google-analitics": "*"
```

to the `require` section of your `composer.json` file.

## Configuring

```php
return [
    //...
    'components' => [
        //...
        'ga' => [
            'class' => 'xcartman\ga\GoogleAnalitics',
			//https://ga-dev-tools.appspot.com/account-explorer/
            'viewId' => '',
			//https://developers.google.com/analytics/devguides/reporting/core/v4/quickstart/service-php#1_enable_the_api			
			'privateKey' => '' 
        ],
    ],
];
```

## Usage

Be sure to look at the [dimension and metrics](https://developers.google.com/analytics/devguides/reporting/core/dimsmets)

### MAKE Simple request and display it

```php
Yii::$app->ga->begin
	->dateRange('7daysAgo', 'today')
	->metric('ga:sessions')
	->dimension('ga:browser')
	->request()
	->printReports();
```

### how to use in the loop it
Begin method call

```php
for($i=0;$i<5;$i++){
    Yii::$app->ga->begin <-- use begin 
}
```

#### Without begin

```php
for($i=0;$i<5;$i++){
    $googleAnalitics = clone Yii::$app->ga; <-- without begin 

    do something

    //free memory
    $googleAnalitics = null;
}
```

#### Wrong way to use it
!Important After each request, you need to clear the data, this is done by the method getBegin or clone ga object
```php
for($i=0;$i<5;$i++){
	Yii::$app->ga
		->dateRange('7daysAgo', 'today')
		->metric('ga:sessions')
		->dimension('ga:browser')
		->request()
		->printReports();
}
```

#### MAKE Simple request and converto to array and display it
```php
  $report = Yii::$app->ga->begin->dateRange('1daysAgo', 'today')
            ->metric('ga:uniqueEvents')
            ->dimension('ga:eventCategory')
            ->dimension('ga:eventAction')
            ->dimension('ga:eventLabel')
            ->request()->one($index = 0);

  print_r($report);
```
#### MAKE Sigment or Filter by data request
```php
	$values = Yii::$app->ga->begin->dateRange('70daysAgo', 'today')
        ->metric('ga:uniqueEvents')
        ->dimension('ga:segment')
        ->segment('ga:eventLabel', 'What are you loking for ?')
        ->request()->one($index = 0);
```


