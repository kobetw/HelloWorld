<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 16/2/22
 * Time: 下午2:03
 */
namespace App\Http\Traits;

use League\Flysystem\Adapter\Local;
use Log;
use Storage;
use Request;
use App\Attachment;

trait FileTrait
{
    private $forbidden_files = array("php","js","sh","py","rb","exe");

    public function create_res_link($disk, $resDir)
    {

        $resRoot = public_path(config("app.res_path"));
        $ret = file_exists($resRoot);

        if (!$ret) {

            Log::debug("make res root" . $resRoot);
            mkdir($resRoot);
        }

        $linkPath = public_path(config("app.res_path") . DIRECTORY_SEPARATOR . $resDir);

        $ret = file_exists($linkPath);

        if ($ret) {
            return true;
        }

        $diskRoot = $disk->getDriver()->getAdapter()->getPathPrefix();

        $this->create_symbol($diskRoot,$linkPath);
//
//        $linkCmd = "ln -s  '" . $diskRoot . "'    '" . $linkPath . "'";
//        Log::debug("create resource symbol link ");
//        $ret = system($linkCmd);
        return true;
    }                                          //2.1

    public function get_disk_path($disk)
    {
        $diskDir = date("Y") . "/" . date("m") . "/" . date("d") . "/";


        if ($disk->exists($diskDir)) {
            Log::debug("exist disk dir " . $diskDir);
            return $diskDir;
        } else {

            Log::debug("not exist disk dir " . $diskDir);
            $ret = $disk->makeDirectory($diskDir);
            if ($ret) {


                Log::debug("make dir success " . $diskDir);
            } else {
                Log::debug("make dir failed " . $diskDir);
            }
        }

        return $diskDir;
    }                                                     //2.2

    public function get_disk_url($filePath, $diskLabel)
    {

        return "/" . _JOIN_PATH(config("app.res_path") , $diskLabel, $filePath);
        // return "/" . config("app.res_path") . "/" . $diskLabel . "/" . $filePath;
    }                                      //2.3

    public function save_to_disk($diskLabel,$diskPath=null,$prefix="f",$fileTag = "file")
    {
        $file = Request::file($fileTag);
        if (empty($file)) {
            Log::debug("no upload file");
            return null;
        }

        if (!empty($file) && $file->isValid()) {

            $result['mime_type'] = $file->getMimeType();
            $result['extension'] = $file->guessExtension();
            $disk = Storage::disk($diskLabel);
            if (empty($disk))
            {
                Log::error("disk ".$diskLabel." is not existed, get the default disk");
                $disk = Storage::disk("data");
            }
            $diskRoot = $disk->getDriver()->getAdapter()->getPathPrefix();
            //$diskPrefix = substr($diskRoot, strlen(public_path()));
            Log::debug("disk root is " . $diskRoot);
            $this->create_res_link($disk,$diskLabel);
            if ($diskPath == null)
            {
                $fileDir = $this->get_disk_path($disk);
            }else
            {
                $fileDir = $diskPath;
                if (!$disk->exists($fileDir)) {
                    Log::debug("not exist disk dir " . $fileDir);
                    $ret = $disk->makeDirectory($fileDir);
                    if ($ret) {
                        Log::debug("make dir success " . $fileDir);
                    } else {
                        Log::debug("make dir failed " . $fileDir);
                    }
                }
            }
            $fileExt = $file->getClientOriginalExtension();
            if (in_array($fileExt,$this->forbidden_files))
            {
                return null;
            }
            $newFileName = uniqid($prefix) . time().(empty($fileExt)?"":".".strtolower($fileExt));
            $destPath = _JOIN_PATH($diskRoot,$fileDir);
            Log::debug("move to dest path :".$destPath);

            //$file->move($diskRoot . $diskPath, $newFileName);
            $file->move($destPath, $newFileName);
            $fullPath =_JOIN_PATH($destPath, $newFileName);
            Log::debug("full  path is  :".$fullPath);
            //$url = url($diskPrefix.$fileDir . $newFileName);
            //$url = $disk->url($fileDir . $newFileName);
            $url = $this->get_disk_url(_JOIN_PATH($fileDir , $newFileName), $diskLabel);
            $fileUrl = env("FILE_URL",config("app.app_url"));
            if (empty($fileUrl))
            {
                $fileUrl = url();
            }
            Log::debug(" disk url is ".$url);
            Log::debug(" file url is ".$fileUrl);

            $url = _JOIN_PATH($fileUrl,$url);
            Log::debug("file url is " . $url);


            $result['title'] = Request::input('file_title');
            $result['desc'] = Request::input('file_desc');
            $result['file'] = $newFileName;
            $result['url']  = $url;
            $result['type'] = $file->getClientOriginalExtension();
            $result['full_path'] = $fullPath;
            $result['disk'] = $diskLabel;
            $result['path'] = $fileDir;
            $result['name'] = $file->getClientOriginalName();
            $result['size'] = $file->getClientSize();
            $result['disk_root'] = $diskRoot;
            $result['hash'] = hash_file('md5', $fullPath);

            Log::debug("upload file",$result);

            Log::debug("file info",(array)$file);

            return $result;
        }

        return null;
    }    //1

    public function create_symbol($path,$linkPath)
    {

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

            $linkCmd = 'mklink /J  "' . $linkPath . '"    "' . $path . '"';
            Log::debug("create resource symbol link  from ".$path."  to ".$linkPath);

            $ret = system($linkCmd);
            return $ret;

        } else {



            $linkCmd = "ln -s  '" . $path . "'    '" . $linkPath . "'";
            Log::debug("create resource symbol link  from ".$path."  to ".$linkPath);
            $ret = system($linkCmd);
            return $ret;
        }


    }                                           //2.1.1




}
