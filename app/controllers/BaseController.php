<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

	public function fileData(){
		// return a partial with tags for file
		$sHash = Input::get("hash");

		if(isset($sHash)){
			// get file data
			$oaFileData = DB::table("files")
			->join("geodata", "files.id", "=", "geodata.file_id")
			->join("tags", "files.id", "=", "tags.file_id")
			->where("files.hash", "=", $sHash)->get();

			return View::make("partials/file-info")->with("filedata", $oaFileData);
		}else{
			return Response::make("", 422);
		}
	}
}