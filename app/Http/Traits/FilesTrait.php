<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 18/07/2017
 * Time: 05:42 PM
 */

namespace App\Http\Traits;

//use App\File;
//use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Exception;

trait FilesTrait {
    
    public function removeFile($file)
    {
        // delete file from server and erase its DB record

        $success = true;
        $file_error = false;

        try {
            \Storage::disk('local')->delete($file->name);
        }
        /*
        catch (ModelNotFoundException $ex) {
            $success = false;
            $file_error = true;
        }
        */
        catch (Exception /*FileNotFoundException*/ $ex) {
            $success = false;
            $file_error = $ex->getMessage(); //If a file can't be deleted send error message and prevent deletion of parent record
        }

        if($success)
            $file->delete();
        
        return $file_error;
    }
    
    public function blockFile($file)
    {
        $file->status = 1; // Block the modification of the file
        $file->save();
    }
    
    public function unblockFile($file)
    {
        $file->status = 0; // Allow file modifications
        $file->save();
    }
}
