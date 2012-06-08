<?php

class RootURLController extends Controller {

	public function handleRequest(SS_HTTPRequest $request, DataModel $model = null) {
		$this->request = $request;
		$this->pushCurrent();

		$this->response = new SS_HTTPResponse();
		$this->init();

		if($this->response->isFinished()) {
			$this->popCurrent();
			return $this->response;
		}

		try {
			$page = StaticTree::get();
			$controller = StaticController::controller_for($page);

			if ($controller) $result = $controller->handleRequest($this->request, null);
			else throw new SS_HTTPResponse_Exception('', 404);
		}
		catch(SS_HTTPResponse_Exception $responseException) {
			$result = $responseException->getResponse();
		}

		$this->popCurrent();
		return $result;
	}
}