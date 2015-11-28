<?php

namespace Schalkt\Scharest;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

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

	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

		$inputs = Request::json()->all();

		$modelName = $this->modelName;
		$model = new $modelName;
		$model->fill($inputs);

		if (!$model->isValid('insert')) {
			return Response::json($model->getErrors(), 400);
		}

		if (!$model->save()) {
			return Response::json($model->getErrors(), 400);
		}

		return Response::json($model, 200);

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
	public function update($id)
	{

		$inputs = Request::json()->all();

		$modelName = $this->modelName;
		$model = $modelName::find($id);

		if (empty($model)) {
			return Response::json($model, 404);
		}

		$model->fill($inputs);

		if (!$model->isValid('update')) {
			return Response::json($model->getErrors(), 400);
		}

		if (!$model->save()) {
			return Response::json($model->getErrors(), 400);
		}

		return Response::json($model, 200);
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


}