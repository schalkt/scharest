<?php

namespace Schalkt\Scharest;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\MessageBag;

/**
 * Class RestfulTraitController
 *
 * @package Schalkt\Scharest
 */
trait RestfulTraitController
{

	/**
	 * @var null
	 */
	protected $entity = null;

	/**
	 * @var array
	 */
	protected $with = array();


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

		// ng-admin query parameters
		$_page = Input::get('_page', 1);
		$_perPage = Input::get('_perPage', 30);
		$_sortDir = Input::get('_sortDir', 'DESC');
		$_sortField = Input::get('_sortField', 'id');
		$_offset = ($_page - 1) * $_perPage;
		$_filters = json_decode(Input::get('_filters', '{}'));

		$modelName = $this->modelName;

		$response = $modelName::with($this->with)
			->orderBy($_sortField, $_sortDir)
			->filters($_filters)
			->offset($_offset)
			->limit($_perPage)
			->get();

		$totalCount = $response->count();
		$content = Response::json($response, 200)->header('X-Total-Count', $totalCount);

		return $content;

	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{

		$modelName = $this->modelName;
		$model = new $modelName;

		return Response::json($model, 200);

	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store($inputs = null, $callback = null)
	{

		if ($inputs === null) {
			$inputs = Request::json()->all();
		}

		$modelName = $this->modelName;
		$model = new $modelName;

		return $this->save($model, $inputs, 'insert', $callback);

	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function show($id)
	{

		$modelName = $this->modelName;
		$result = $modelName::with($this->with)->where('id', $id)->first();

		if (empty($result)) {
			return Response::json($result, 404);
		} else {
			return Response::json($result, 200);
		}

	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function edit($id)
	{

		$modelName = $this->modelName;
		$result = $modelName::with($this->with)->where('id', $id)->first();

		if (empty($result)) {
			return Response::json($result, 404);
		} else {
			return Response::json($result, 200);
		}

	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function update($id, $inputs = null, $callback = null)
	{

		if ($inputs === null) {
			$inputs = Request::json()->all();
		}

		$modelName = $this->modelName;
		$model = $modelName::find($id);

		if (empty($model)) {
			return Response::json($model, 404);
		}

		return $this->save($model, $inputs, 'update', $callback);

	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function destroy($id)
	{

		$modelName = $this->modelName;
		$model = $modelName::find($id);

		if (empty($model)) {
			return Response::json($model, 404);
		}

		$result = $model->delete();

		if (!$result) {
			return Response::json($result, 400);
		}

		return Response::json($result, 200);

	}

	/**
	 * Common save method for update and insert action
	 *
	 * @param $model
	 * @param $inputs
	 * @param $action
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function save($model, $inputs, $action, $callback = null)
	{

		try {

			$model->fill($inputs);

			if (!$model->isValid($action)) {
				return Response::json($model->getErrors(), 400);
			}

			if (!$model->save()) {
				return Response::json($model->getErrors(), 400);
			}

			if (is_callable($callback)) {
				$callback($model);
			}

			return Response::json($model, 200);

		} catch (\Exception $e) {

			return $this->responseException($e);

		}

	}

	/**
	 * Response exception message and code
	 *
	 * @param $e
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function responseException($e)
	{

		$messageBag = new MessageBag;
		$messageBag->add('exception', $e->getMessage());

		return Response::json($messageBag->toArray(), $e->getCode());

	}

}
