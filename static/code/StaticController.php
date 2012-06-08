<?php

class StaticController extends Controller {

	protected $page;

	public function __construct($page) {
		$this->page = $this->failover = $page;
		parent::__construct();
	}

	public function handleRequest(SS_HTTPRequest $request, DataModel $model = null) {
		$child  = null;
		$action = $request->param('Action');

		if ($action && !$this->hasAction($action)) {
			$child = $this->page->Child($action);

			if($child) {
				$request->shiftAllParams();
				$request->shift();

				return self::controller_for($child)->handleRequest($request, $model);
			}
		}

		Director::set_current_page($this->page);
		$response = parent::handleRequest($request, $model);
		Director::set_current_page(null);
		return $response;
	}

	public static function controller_for(StaticTree $page) {
		if(get_class($page) == 'StaticTree') $controller = "StaticController";
		else $controller = get_class($page)."_Controller";

		if (class_exists($controller)) return new $controller($page);
	}
}
