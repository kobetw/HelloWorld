<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Http\Traits\FileTrait;



/**
 * Created by PhpStorm.
 * User: DQ221
 * Date: 2017/6/13
 * Time: 13:57
 */
class StudentController extends Controller
{
    use FileTrait;
    public function test(){
        return view("student.welcome");
    }


    public function test_siderbar(){
        return view("layouts.sidebar");
    }
    /**
     * 学生登录后首页(自己所选的全部课程)
     */
    public function index(){


    }

    /**
     * 所有的课程
     */
    public function allcourse(){

        $courses = DB::table("tb_courses")->get();
        $data['courses'] = $courses;

        return view("client.course",$data);
    }

    /**
     * 添加课程
     * @return View
     */
    public function addcourse(){


        return view('admin/addcourse');
    }


    public function simditor_upload(Request $request)
    {
        $type = $request->input('type',"");
        $file = $request->input('file',"");
        $result = array(
            "success" => false,
            "msg" => "upload failed",
            "file_path" => array(),
            "type" => $type,
            "file" =>$file,
            "files"=>$_FILES
        );
        $diskLabel = "data";
        $rst  = $this->save_to_disk($diskLabel);
        if (!empty($rst))
        {
            $result['success'] = true;
            $result["msg"] = "upload file success";
            array_push($result['file_path'],   $rst['url']);
        }else
        {
            $result["msg"] = "upload file failed";
            $result['success'] = false;
        }
        return response()->json($result);
    }






}

