<?php
define ('PHPCOVERAGE_HOME', dirname(__FILE__).'/../inc/spikephpcoverage/');
// require_once "spikephpcoverage/samples/local/phpcoverage.inc.php";
require_once PHPCOVERAGE_HOME . "/CoverageRecorder.php";
require_once PHPCOVERAGE_HOME . "/reporter/HtmlCoverageReporter.php";

$project = realpath(dirname(__FILE__) . '/../');
$reporter = new HtmlCoverageReporter("Code Coverage Report", "", "report");
$includePaths = array($project);
$excludePaths = array($project.'/inc/simpletest/',
                      $project.'/inc/spikephpcoverage/',
                      $project.'/index.php',
                      $project.'/tests/',
                      $project.'/inc/api/vendor/',
                      $project.'/tests/JunitXMLReporter.php',
                 );
$cov = new CoverageRecorder($includePaths, $excludePaths, $reporter);
$cov->startInstrumentation();
