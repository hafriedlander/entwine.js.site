<?php


class Page extends StaticTree {

}

class Page_Controller extends StaticController {
	public function init() {
		parent::init();

		Requirements::css('themes/entwine/fonts/Quicksand_Light.css');
		Requirements::css('themes/entwine/fonts/Quicksand_Book.css');

		Requirements::css('static/code/markdown/nijikodo/css/mac-classic.css');

		Requirements::css('themes/entwine/css/screen.css');

		Requirements::javascript('mysite/javascript/jquery-1.7.2.js');
	}
}