<?php

require_once 'thirdparty/zend_translate_railsyaml/library/Translate/Adapter/thirdparty/sfYaml/lib/sfYamlParser.php';

class StaticTree extends StaticModel {

	static $fields = array(
		'Title' => 'Varchar',
		'MenuTitle' => 'Varchar',
		'Content' => 'MarkdownText'
	);

	static $root = 'mysite/content';
	static $idmap = array();

	protected $path;

	static function fullPath($path) {
		return BASE_PATH . DIRECTORY_SEPARATOR . self::$root . ($path ? DIRECTORY_SEPARATOR . $path : '');
	}

	static function load($fullPath) {
		$parser = new sfYamlParser();
		return $parser->parse(file_get_contents($fullPath));
	}

	static function get($path = '') {
		if ($path == '.') $path = '';
		$path = preg_replace('/\.[^\.]+$/', '', $path);
		$path = preg_replace('/\/index$/', '', $path);

		$fullPath = self::fullPath($path);
		$data = null;

		$key = $path ? $path : '<root>';
		if (array_key_exists($key, self::$idmap)) return self::$idmap[$key];

		if (is_dir($fullPath)) {
			$index = $fullPath . DIRECTORY_SEPARATOR . 'index.yml';
			if (is_file($index)) $data = self::load($index);
		}
		else if (is_file($fullPath.'.yml')) {
			$data = self::load($fullPath.'.yml');
		}
		else return;

		$type = $data && isset($data['Type']) ? $data['Type'] : 'Page';
		return self::$idmap[$path] = new $type($path, $data);
	}

	function __construct($path = '', $data = null) {
		$this->path = $path;
		parent::__construct($data);
	}

	function getFullPath() {
		return self::fullPath($this->path);
	}

	function Child($name) {
		$base = $this->path ? $this->path . DIRECTORY_SEPARATOR : '';
		return self::get($base.$name);
	}

	protected $children = null;

	function Children() {
		if (!$this->children) {
			$this->children = new ArrayList();
			$base = $this->path ? $this->path . DIRECTORY_SEPARATOR : '';

			if (is_dir($this->getFullPath()) && $handle = opendir($this->getFullPath())) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry == '.' || $entry == '..' || $entry == 'index.yml') continue;
					if ($child = self::get($base . $entry)) $this->children->push($child);
				}
			}
		}

		return $this->children;
	}

	function Parent() {
		if ($this->path) return self::get(dirname($this->path));
	}

	function Menu($level = 1) {
		if ($level == 1) {
			return self::get()->Children();
		}
		else {
			$stack = array();
			$parent = $this;

			do {
				array_unshift($stack, $parent);
			}
			while ($parent = $parent->Parent());

			if (isset($stack[$level-1])) return $stack[$level-1]->Children();
		}
	}

	function Ancestor($level = 1) {
		$paths = explode('/', $this->path);
		return self::get(implode('/', array_slice($paths, 0, $level)));
	}

	function MenuTitle() {
		if ($value = $this->getField("MenuTitle")) return $value;
		if ($value = $this->Title) return $value;
		return basename($this->path);
	}

	function Link() {
		return $this->path;
	}

	public function isCurrent() {
		return $this === Director::get_current_page();
	}

	public function isSection() {
		if ($this->isCurrent()) return true;

		$page = Director::get_current_page();
		if ($page instanceof StaticTree && strpos($this->path, $page->path) === 0) return true;
	}

	public function LinkOrCurrent() {
		return $this->isCurrent() ? 'current' : 'link';
	}

	public function LinkOrSection() {
		return $this->isSection() ? 'section' : 'link';
	}


}