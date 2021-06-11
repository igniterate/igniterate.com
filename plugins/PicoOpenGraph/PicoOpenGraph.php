<?php

/**
 * OpenGraph Plugin adds auto generated OG Meta Tags for better Facebook Sharing right before closing of head.
 *
 * Old version appeared to be incompatible with current Pico code, which expects different function names
 * for a plugin. New version fixes this, and adds a functionality:
 * Allows individual pages' meta data to define an "Image" value, which will be used for the open graph
 * share image when defined. This allows it to not have to parse through the page for images, and allows
 * you to give it a specific image to use for sharing.
 *
 * @author  old version Ahmet Topal, new version Bigi Lui
 * @link    (old) https://github.com/ahmet2106/pico-opengraph/blob/master/at_opengraph.php
 * @link    (new) https://github.com/bigicoin/PicoMiscPlugins
 * @license MIT
 */
final class PicoOpenGraph extends AbstractPicoPlugin
{
	/**
	 * This plugin is enabled by default?
	 *
	 * @see AbstractPicoPlugin::$enabled
	 * @var boolean
	 */
	protected $enabled = false;

	/**
	 * This plugin depends on ...
	 *
	 * @see AbstractPicoPlugin::$dependsOn
	 * @var string[]
	 */
	protected $dependsOn = array();

	// bool
	private $is_homepage;
	private $is_error = false;
	
	// array
	private $config = array();
	private $meta = array();
	
	// string
	private $url;
	
	/**
	 * Triggered when Pico reads its known meta header fields
	 *
	 * @see    Pico::getMetaHeaders()
	 * @param  string[] &$headers list of known meta header
	 *     fields; the array value specifies the YAML key to search for, the
	 *     array key is later used to access the found value
	 * @return void
	 */
	public function onMetaHeaders(array &$headers)
	{
		$headers['image'] = 'Image';
	}

	/**
	 * Triggered after Pico has evaluated the request URL
	 *
	 * @see    Pico::getRequestUrl()
	 * @param  string &$url part of the URL describing the requested contents
	 * @return void
	 */
	public function onRequestUrl(&$url)
	{
		$this->url = $url;
		$this->is_homepage = ($url == '') ? true : false;
	}
	
	/**
	 * Triggered before Pico reads the contents of a 404 file
	 *
	 * @see    Pico::load404Content()
	 * @see    DummyPlugin::on404ContentLoaded()
	 * @param  string &$file path to the file which contents were requested
	 * @return void
	 */
	public function on404ContentLoading(&$file)
	{
		$this->is_error = true;
	}
	
	/**
	 * Triggered after Pico has read its configuration
	 *
	 * @see    Pico::getConfig()
	 * @param  array &$config array of config variables
	 * @return void
	 */
	public function onConfigLoaded(array &$config)
	{
		$this->config = $config;
		if (substr($this->config['base_url'], strlen($this->config['base_url']) - 1) == '/') {
			$this->config['base_url'] = substr($this->config['base_url'], 0, strlen($this->config['base_url']) - 1);
		}
	}
	
	/**
	* Triggered after Pico has parsed the meta header
	*
	* @see    Pico::getFileMeta()
	* @param  string[] &$meta parsed meta data
	* @return void
	*/
	public function onMetaParsed(array &$meta)
	{
		$this->meta = $meta;
	}
	
	/**
	 * Triggered after Pico has parsed the contents of the file to serve
	 *
	 * @see    Pico::getFileContent()
	 * @param  string &$content parsed contents
	 * @return void
	 */
	public function onContentParsed(&$content)
	{
		$images = array();

		if (!empty($this->meta['image'])) {

			// if meta info for "Image" defined, just use that, save the time to parse the page
			$hasProtocol = $this->startsWith($this->meta['image'], array('http://', 'https://'));
			$images[] = sprintf('%s%s%s',
				$hasProtocol ? '' : $this->config['base_url'],
				(!$hasProtocol && !$this->startsWith($this->meta['image'], array('/'))) ? '/'.$this->url : '',
				$this->meta['image']
			);

		} else {

			// if not, we gotta parse the page for any images
			preg_match_all('/<img[^>]+>/i', $content, $img_tags);
			
			foreach ($img_tags[0] as $img_tag)
			{
				preg_match('/src="([^"]*)"/i', $img_tag, $match);
				$src = $match[1];
				
				$hasProtocol = $this->startsWith($src, array('http://', 'https://'));
				$images[] = sprintf('%s%s%s',
					$hasProtocol ? '' : $this->config['base_url'],
					(!$hasProtocol && !$this->startsWith($src, array('/'))) ? '/'.$this->url : '',
					$src
				);
			}
			
			if (isset($this->config['opengraph_default_image'])) {
				$images[] = $this->config['opengraph_default_image'];
			}

		}
		
		$this->images = $images;
	}
	
	/**
	 * Triggered after Pico has rendered the page
	 *
	 * @param  string &$output contents which will be sent to the user
	 * @return void
	 */
	public function onPageRendered(&$output)
	{
		// only if is not an error
		if (!$this->is_error) {
			$properties = array(
				'og:type'				=> $this->is_homepage ? 'website' : 'article',
				'og:title'				=> $this->meta['title'],
				'og:description'		=> $this->meta['description'],
				'og:url'				=> sprintf('%s/%s', $this->config['base_url'], $this->url),
				'og:site_name'			=> $this->config['site_title']
			);
			
			$meta = '';
			
			foreach ($properties as $prop_k => $prop_v) {
				$meta .= "\t". sprintf('<meta property="%s" content="%s" />', $prop_k, $prop_v).PHP_EOL;
			}

			for ($i = 0; $i < count($this->images); $i++) {
				$meta .= "\t". sprintf('<meta property="%s" content="%s" />', 'og:image', $this->images[$i]).PHP_EOL;
			}
			
			// just replace closing of head with og meta tags and close head, again
			$output = str_replace('</head>', PHP_EOL.$meta.'</head>', $output);
		}
	}

	// string start with one elem of array
	private function startsWith($string, $start_with = array())
	{
		foreach ($start_with as $start) {
			if (strncmp($string, $start, strlen($start)) === 0) return true;
		}
		
		return false;
	}
}