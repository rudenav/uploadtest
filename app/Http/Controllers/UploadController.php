<?php

namespace App\Http\Controllers;

use App\Http\Models\RemoteSiteModel;

class UploadController extends Controller
{
    /**
     * @param RemoteSiteModel $remoteSite
     * @param string $filename
     */
    public function uploadFile(RemoteSiteModel $remoteSite, $filename)
    {
        for($i = 0; $i < 10; $i++) {
            $remoteSite
                ->fetchForm()
                ->extractAuthKey()
                ->sendForm(storage_path($filename), 'It really works !!!');
        }
    }
}
