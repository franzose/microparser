<?php

/**
 * Parses sites by given URLs.
 * 
 * The function can get a document fragment, not only a whole page,
 * because it uses XPath expression to traverse nodes.
 * 
 * Use the function in such a manner:
 * 
 * <code>
 * $results = parse_site(array(
 *     'habrahabr' => array(
 *         'url' => 'http://habrahabr.ru',
 *         'xpath' => 'xpath/to/node',
 *         'xsl' => 'path/to/xsl'
 *     ),
 *     'stackoverflow' => array(
 *         'url' => 'http://stackoverflow.com',
 *         'xpath' => 'xpath/to/node',
 *         'xsl' => ''
 *     ),
 *     'gismeteo' => array(
 *         'url' => 'http://gimeteo.ru',
 *         'xpath' => 'xpath/to/node',
 *         'xsl' => 'path/to/xsl'
 *     )
 * ));
 *
 * print $results['habrahabr'];
 * </code>
 * 
 * @param array $sites An array with sites' options.
 *  It must contain arrays with the following keys:<br>
 *  — <b>site_name</b>: the key that's used for the further use of the results;<br>
 *  — <b>url</b>: site's url;<br>
 *  — <b>xpath</b>: XPath expression (not required, used if it's necessary to get just a document fragment);<br>
 *  — <b>xsl</b>: <strong>absolute path</strong> to XSLT file (not required, used if it's necessary to make a transformation);<br>
 *  — <b>xslparams</b>: params to be brought to XSLTProcessor (not required)
 *       array('paramname' => array(
 *                 'namespace' => 'somens' //not required
 *                 'value' => 'somevalue'
 *       ))
 *  — <b>transform</b>: if exists and set to 'false', then the transformation won't be done even if default options are present
 * 
 * @param array $defaults An array containing default values for <b>xpath</b> and <b>xsl</b> keys. Any other key is ignored.
 * @return array
 */
function parse_site(array $sites, array $defaults = array()){
	$results = array();
	
	foreach($sites as $name => $params){
		$do_not_transform = (isset($params['transform']) && $params['transform'] === false);
		
		if ( !isset($params['xpath']) && isset($defaults['xpath']))
			$params['xpath'] = $defaults['xpath'];
		
		if ( !isset($params['xsl']) && isset($defaults['xsl']) && !$do_not_transform)
			$params['xsl'] = $defaults['xsl'];
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $params['url']);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		
		$html = curl_exec($ch);
		curl_close($ch);
		
		$domdoc = new DOMDocument();
		$domdoc->loadHTML($html);
		$xpath = new DOMXPath($domdoc);
		$fragment = (isset($params['xpath'])) ? $xpath->query($params['xpath']) : $html;
		$reshtml = ($fragment instanceof DOMNodeList) ? $domdoc->saveHTML() : $html;
		
		if ( !isset($params['xsl'])){
			$results[$name] = $reshtml;
		}
		elseif ( !file_exists($params['xsl'])){
			throw new Exception('The XSLT stylesheet does not exists. Path:'.$params['xsl']);
		}
		else {
			//we need to recreate document
			//and load XML to avoid non-latin mess
			$domdoc = new DOMDocument();
			$domdoc->loadXML($reshtml);
			$xsltproc = new XSLTProcessor();
			$xsl = new DOMDocument();
			$xsl->load($params['xsl']);
			$xsltproc->importStylesheet($xsl);

			if (isset($params['xslparams'])){
				foreach($params['xslparams'] as $param_name => $param_opts){
					$ns = isset($param_opts['namespace']) ? $param_opts['namespace'] : '';
					$xsltproc->setParameter($ns, $param_name, $param_opts['value']);
				}
			}

			$results[$name] = $xsltproc->transformToXml($domdoc);
		}		
	}
	
	return $results;
}
