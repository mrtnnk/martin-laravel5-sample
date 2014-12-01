<?php namespace App\Gestion;

class BaseGestion {

	/**
	 * The Model instance.
	 *
	 * @var Illuminate\Database\Eloquent\Model
	 */
	protected $model;

	/**
	 * Get number of records.
	 *
	 * @return int
	 */
	public function getNumber()
	{
		$total = $this->model->all()->count();
		$new = $this->model->whereVu(0)->count();
		return compact('total', 'new');
	}

	/**
	 * Destroy a model.
	 *
	 * @param  int $id
	 * @return void
	 */
	public function destroy($id)
	{
		$this->model->findOrFail($id)->delete();
	}

}