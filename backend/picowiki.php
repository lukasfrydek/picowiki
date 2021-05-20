<?php

class PicoWiki
{
	public $config = null; // configuration variables
	public $file_list = []; // array of available files
	public $plugin_list = [];
	public $url = null;
	public $html = null;
	public $events = [];

	public function __construct($config)
	{
		$this->event('init', $this);
		$this->config = $config;
		$this->event('config_loaded', $this);
		$this->loadPlugins();
		$this->event('plugins_loaded', $this);
	}

	/**
	 * Finds a file with that URL and outputs it nicely
	 *
	 * @param string $url a URL slug that should presumably match a file
	 */
	public function run() {
		// dd(CURRENT_URL);
		$this->event('run_init', $this);
		$this->url = preg_replace('/[^a-z0-9-\/]/', '', strtolower(CURRENT_URL));
		$this->event('url_loaded', $this);
		$this->file_list = $this->listFiles();
		$file_path = $this->getFilePath($this->url);
		// dd($file_path);
		$this->event('list_loaded', $this);
		$this->view($file_path);
	}

	/**
	 * Reads all files in the path
	 *
	 * @param string $path a glob path pattern
	 */
	protected function listFiles()
	{
		$this->file_list = $this->readDirectory();

		$this->file_list = array_map(function ($f) {
			$f = str_replace(PATH_FILES, '', $f);
			$f = str_replace('index'.MD, '', $f);
			$f = str_replace(MD, '', $f);
			$f = trim($f, '/');
			return $f;
		}, $this->file_list);

		sort($this->file_list);
		return $this->file_list;
	}

	/**
	 * Returns a list of all files that match a pattern, recursive
	 *
	 * @param string $pattern a glob path pattern
	 */
	protected function readDirectory($pattern = null) {
		$pattern = $pattern !== null ? $pattern : $this->getPath('*');
		$files = glob($pattern);
		$dirs = glob(dirname($pattern).'/*', GLOB_ONLYDIR);
		// dd($pattern, $files, $dirs);

		foreach ($dirs as $dir) {
			$files = array_merge($files, $this->readDirectory( $this->getPath('*', $dir.'/') ));
		}

		// dd($files);
		return $files;
	}

	/**
	 * Returns the full path to a file in /files/ folder based on its filename
	 *
	 * @param string $file_name file name to get the full path from
	 */
	protected function getFilePath($file_name, $path = null) {

		// dd($file_name, dirname($file_name));

		if(!file_exists($this->getPath($file_name))) {
			$file_name = file_exists($this->getPath($file_name.DS.'index')) ? $file_name.DS.'index' : 404;
		}
		// $file_name = file_exists($this->getPath($file_name)) ? $file_name : dirname($file_name).'index';

		if ($file_name === 404 || !file_exists($this->getPath($file_name))) {
			http_response_code(404);
			$file_path = $this->get404Path();
		} else {
			$file_path = $this->getPath( $file_name );
		}

		return $file_path;
	}

	protected function get404Path() {
		return $this->getTemplatePath('404', MD);
	}

	/**
	 * Compose File Path
	 *
	 * @param string $file_name
	 * @param string $path
	 * @param string $ext
	 * @return string
	 */
	protected function getPath($file_name, $path = null, $ext = null) {
		$path = $path !== null ? $path : PATH_FILES;
		$ext = $ext !== null ? $ext : MD;
		return $path . $file_name . $ext;
	}
	
	protected function getTemplatePath($template, $ext = PHP) {
		return $this->getPath($template, PATH_TEMPLATES, $ext);
	}
	/**
	 * Outputs the templates and files
	 * You can use file_get_contents($file_path) instead of require to disable running PHP code in .md files
	 *
	 * @param string $file_path full path to the Markdown file
	 */
	protected function view($file_path)
	{
		require $this->getTemplatePath('_header');
		ob_start();
		require $file_path;
		$this->html = ob_get_clean();
		$this->html = $this->event('view_after', $this);
		echo $this->html;
		require $this->getTemplatePath('_footer');
	}

	/**
	 * Finds .php files inside the /plugins/ folder, stores the list and initializes them
	 */
	protected function loadPlugins() {
		$this->plugin_list = glob( PATH_PLUGINS . '*' . PHP);
		foreach ($this->plugin_list as $plugin_file) {
			require_once $plugin_file;
			$class_name = pathinfo($plugin_file)['filename'];
			call_user_func_array([$class_name, 'run'], [$this]);
		}
	}

	/**
	 * Attach (or remove) multiple callbacks to an event and trigger those callbacks when that event is called.
	 * https://github.com/Xeoncross/micromvc/blob/master/Common.php#L15
	 *
	 * @param string $event name
	 * @param mixed $value the optional value to pass to each callback
	 * @param mixed $callback the method or function to call - FALSE to remove all callbacks for event
	 */
	public function event($event, $value = NULL, $callback = NULL)
	{
		// Adding or removing a callback?
		if ($callback !== NULL) {
			if ($callback) {
				$this->events[$event][] = $callback;
			} else {
				unset($this->events[$event]);
			}
		} elseif (isset($this->events[$event])) { // Fire a callback
			foreach($this->events[$event] as $function) {
				$value = call_user_func($function, $value);
			}
			return $value;
		}
	}
}
