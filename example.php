<?php

include 'parse_site.php';

$results = parse_site(array(
    'zona_vostok' => array(
        'url' => 'http://www.championat.com/football/_russia2d/589/table/all.html',
        'xpath' => 'xpath' => '//div[@id="section-statistics"]/table[2]',
        'xsl' => __DIR__.'/football.xsl'
));
 
print $results['zona_vostok'];