<?php

class MarkdownText extends HTMLText {
	static $flushed = false;

	static function flush_cache() {
		if (!self::$flushed) {
			$dir = dir(TEMP_FOLDER);

			while (false !== ($file = $dir->read())) {
				if (strpos($file, '_markdown_') === 0) { unlink(TEMP_FOLDER.'/'.$file); }
			}

			self::$flushed = true;
		}
	}

	function __construct($name = null, $options = array()) {
		if (isset($_GET['flush']) && $_GET['flush'] == 'all') self::flush_cache();
		parent::__construct($name, $options);
	}

	function highlight($matches) {
		return "<div class='code code-{$matches[1]}'>" . Nijikodo\Parser::toHtml(trim($matches[2]), $matches[1]) . "</div>";
	}

	function setValue($value, $record = null) {
		$cachefile = TEMP_FOLDER . '/_markdown_'.sha1($value);

		if (file_exists($cachefile) && !isset($_GET['flush'])) return parent::setValue(file_get_contents($cachefile), $record);

		require_once dirname(__FILE__).'/markdown/nijikodo/lib/Nijikodo/Parser.php';
		$value = preg_replace_callback('/```([a-z]+)(.*?)```/s', array($this, 'highlight'), $value);

		require_once dirname(__FILE__).'/markdown/markdown.php';
		require_once dirname(__FILE__).'/markdown/smartypants.php';
		$value = SmartyPants(Markdown($value));

		file_put_contents($cachefile, $value);
		return parent::setValue($value, $record);
	}

}