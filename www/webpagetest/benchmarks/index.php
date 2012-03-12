<?php
chdir('..');
include 'common.inc';
include './benchmarks/data.inc.php';
$page_keywords = array('Benchmarks','Webpagetest','Website Speed Test','Page Speed');
$page_description = "WebPagetest benchmarks";
$aggregate = 'avg';
if (array_key_exists('aggregate', $_REQUEST))
    $aggregate = $_REQUEST['aggregate'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>WebPagetest - Benchmarks</title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, performance site web, internet performance, website performance, web applications testing, web application performance, Internet Tools, Web Development, Open Source, http viewer, debugger, http sniffer, ssl, monitor, http header, http header viewer">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">
        <?php $gaTemplate = 'About'; include ('head.inc'); ?>
        <script type="text/javascript" src="/js/dygraph-combined.js"></script>
        <style type="text/css">
        .chart-container { clear: both; width: 875px; height: 350px; margin-left: auto; margin-right: auto; padding: 0;}
        .benchmark-chart { float: left; width: 700px; height: 350px; }
        .benchmark-legend { float: right; width: 150px; height: 350px; }
        </style>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Benchmarks';
            include 'header.inc';
            ?>
            
            <div class="translucent">
            <?php
            $benchmarks = GetBenchmarks();
            $count = 0;
            foreach ($benchmarks as &$benchmark) {
                if (array_key_exists('title', $benchmark))
                    $title = $benchmark['title'];
                else
                    $title = $benchmark['name'];
                $bm = urlencode($benchmark['name']);
                echo "<h2><a href=\"view.php?benchmark=$bm\">$title</a></h2>\n";
                if (array_key_exists('description', $benchmark))
                    echo "<p>{$benchmark['description']}</p>\n";
                
                if ($benchmark['expand'] && count($benchmark['locations'] > 1)) {
                    foreach ($benchmark['locations'] as $location => $label) {
                        if (is_numeric($label))
                            $label = $location;
                        DisplayBenchmarkData($benchmark, $location, $label);
                    }
                } else {
                    DisplayBenchmarkData($benchmark);
                }
            }
            ?>
            </div>
            
            <?php include('footer.inc'); ?>
        </div>
    </body>
</html>

<?php
/**
* Display the charts for the given benchmark
* 
* @param mixed $benchmark
*/
function DisplayBenchmarkData(&$benchmark, $loc = null, $title = null) {
    global $count;
    global $aggregate;
    $label = 'Speed Index (First View)';
    $chart_title = '';
    if (isset($title))
        $chart_title = "title: \"$title (First View)\",";
    $tsv = LoadDataTSV($benchmark['name'], 0, 'SpeedIndex', $aggregate, $loc);
    if (!isset($tsv) || !strlen($tsv)) {
        $label = 'Time to onload (First View)';
        $tsv = LoadDataTSV($benchmark['name'], 0, 'docTime', $aggregate, $loc);
    }
    if (isset($tsv) && strlen($tsv)) {
        $count++;
        $id = "g$count";
        echo "<div class=\"chart-container\"><div id=\"$id\" class=\"benchmark-chart\"></div><div id=\"{$id}_legend\" class=\"benchmark-legend\"></div></div>\n";
        echo "<script type=\"text/javascript\">
                $id = new Dygraph(
                    document.getElementById(\"$id\"),
                    \"" . str_replace("\t", '\t', str_replace("\n", '\n', $tsv)) . "\",
                    {drawPoints: true,
                    rollPeriod: 1,
                    showRoller: true,
                    labelsSeparateLines: true,
                    $chart_title
                    labelsDiv: document.getElementById('{$id}_legend'),
                    legend: \"always\",
                    xlabel: \"Date\",
                    ylabel: \"$label\"}
                );
              </script>\n";
    }
    if (!array_key_exists('fvonly', $benchmark) || !$benchmark['fvonly']) {
        $label = 'Speed Index (Repeat View)';
        if (isset($title))
            $chart_title = "title: \"$title (Repeat View)\",";
        $tsv = LoadDataTSV($benchmark['name'], 1, 'SpeedIndex', $aggregate, $loc);
        if (!isset($tsv) || !strlen($tsv)) {
            $label = 'Time to onload (Repeat View)';
            $tsv = LoadDataTSV($benchmark['name'], 1, 'docTime', $aggregate, $loc);
        }
        if (isset($tsv) && strlen($tsv)) {
            $count++;
            $id = "g$count";
            echo "<div class=\"chart-container\"><div id=\"$id\" class=\"benchmark-chart\"></div><div id=\"{$id}_legend\" class=\"benchmark-legend\"></div></div>\n";
            echo "<script type=\"text/javascript\">
                    $id = new Dygraph(
                        document.getElementById(\"$id\"),
                        \"" . str_replace("\t", '\t', str_replace("\n", '\n', $tsv)) . "\",
                        {drawPoints: true,
                        rollPeriod: 1,
                        showRoller: true,
                        labelsSeparateLines: true,
                        $chart_title
                        labelsDiv: document.getElementById('{$id}_legend'),
                        legend: \"always\",
                        xlabel: \"Date\",
                        ylabel: \"$label\"}
                    );
                  </script>\n";
        }
    }
}
?>