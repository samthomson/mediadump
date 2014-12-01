<?php

class DeleteProcessor extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public static function process($iFileID)
	{		
		try
		{
			$oFile = FileModel::find($iFileID);


			if(File::exists($oFile->path))
			{
				//File::delete($oFile->path);
				unlink($oFile->path);

				if(File::exists($oFile->path))
					return false;
				else{
					$oFile->have_original = false;
					return true;
				}
				
			}else{
				// already deleted, do nothing other than update our records
				//echo "deleted file";
				// original file has already been deleted

				
				// file no longer exists, remove it from system
				//// ah don't do this!!!!!!! $oFile->removeFromSystem();

				$oFile->have_original = false;
				$oFile->save();
				return true;
			}
		}
		catch(Exception $ex)
		{
			//print_r($ex);
			$eProcessingFailed = new ErrorModel();
			$eProcessingFailed->name = "error - delete processor";
			$eProcessingFailed->message = (string)$ex;
			$eProcessingFailed->value = "0";
			$eProcessingFailed->save();

			return false;
		}
	}
}
