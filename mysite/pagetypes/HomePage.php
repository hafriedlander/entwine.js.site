<?php

class HomePage extends Page {
}

class HomePage_Controller extends Page_Controller {
	public function init() {
		parent::init();

		Requirements::javascript('mysite/javascript/handlebars-1.0.0.beta.6.js');
		Requirements::javascript('mysite/javascript/homepage.js');
	}
}