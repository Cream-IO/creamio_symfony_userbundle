<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use SebastianBergmann\CodeCoverage\CodeCoverage;

class CoverageContext implements Context
{
    /**
     * @var CodeCoverage
     */
    private static $coverage;

    /** @BeforeSuite */
    public static function setup()
    {
        self::$coverage = new CodeCoverage();
        self::$coverage->filter()->addDirectoryToWhitelist(__DIR__.'/../../src');
        self::$coverage->filter()->removeDirectoryFromWhitelist(__DIR__.'/../../src/Migrations');
        self::$coverage->filter()->removeDirectoryFromWhitelist(__DIR__.'/../../src/DataFixtures');
    }

    /** @AfterSuite */
    public static function tearDown()
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade();
        $writer->process(self::$coverage, __DIR__.'/../../docs/code_coverage');
    }

    private function getCoverageKeyFromScope(BeforeScenarioScope $scope)
    {
        $name = $scope->getFeature()->getTitle().'::'.$scope->getScenario()->getTitle();

        return $name;
    }

    /**
     * @BeforeScenario
     */
    public function startCoverage(BeforeScenarioScope $scope)
    {
        self::$coverage->start($this->getCoverageKeyFromScope($scope));
    }

    /** @AfterScenario */
    public function stopCoverage()
    {
        self::$coverage->stop();
    }
}
