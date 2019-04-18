<?php

use Illuminate\Http\Request;
use App\Temployee;
use App\OmrModels\Subject;
use Illuminate\Support\Facades\Input;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|150
|
*/


// use App\OmrModels\Employee;
// use App\Http\Resources\Employee as UserResource;
// Route::get('testapi',function(){
// 	return DB::table('t_student')->limit(20)->get();
// });
	/*OMR*/
	Route::get('/search', function() {	
		// $emp = Temployee::limit('9146')->get();
		// // $emp->createIndex($shards = null, $replicas = null);

		// // $emp->putMapping($ignoreConflicts = true);
	 //    $emp->addToIndex();
	     // $all_books = Temployee::searchByQuery(array('match' => array('EMPLOYEE_ID' => '9140')));
	     $all_books = Temployee::where('EMPLOYEE_ID','9140')->get();
   		 return $all_books->all();
 	// $emp = Temployee::search('GURRAPU SALA');
  //   return $emp->totalHits();
		// Temployee::addAllToIndex();
		// return $emp->searchByQuery(['match' => ['query' => '1']]);

});
	Route::get('/getDetails', 'GetComponentDetailController@getData');
	Route::post('userLogin', 'AuthController@tokenAuthAttempt');
	Route::post('uploadResults','AuthController@upload');
	/*OMR Result Application*/
		Route::get('sendmessage','OmrControllers\ResultController@sendmessage');
	
	Route::post('resultLogin', 'OmrControllers\ResultController@login');
	Route::group([ 'middleware' => 'auth:token' ], function () 
	{	
		/*OMR Result Application*/	
		Route::post('showProfile','redisController@showProfile');
		Route::post('total_percentage','OmrControllers\ResultController@total_percentage');
		Route::post('campus','OmrControllers\ResultController@campus');
		Route::post('modelist','OmrControllers\ResultController@modelist');
		Route::post('mdexamlist','OmrControllers\ResultController@mdexamlist');
		Route::post('campuslist','OmrControllers\ResultController@campuslist');
		Route::post('employeelist','OmrControllers\ResultController@employeelist');
		Route::post('employeecombination','OmrControllers\ResultController@employeecombination');
		Route::post('details','OmrControllers\ResultController@details');
		Route::post('md_sectionlist','OmrControllers\ResultController@campus');
		Route::post('md_studlist','OmrControllers\ResultController@campus');
		Route::post('md_adroitlist','OmrControllers\ResultController@md_adroitlist');
		Route::post('md_total_percentage','OmrControllers\ResultController@md_total_percentage');
		Route::get('subject/{exam_id}/{STUD_ID}','OmrControllers\ResultController@subject');
		Route::post('answer_details','OmrControllers\ResultController@AnswerDetails');
		Route::post('exam_info','OmrControllers\ResultController@exam_info');
		// Route::post('teacher_exam_info','OmrControllers\ResultController@teacher_exam_info');
		Route::post('examlist','OmrControllers\ResultController@examlist');
		Route::post('test_type_list','OmrControllers\ResultController@test_type_list');
		// Route::post('teacher_totalpercentage','OmrControllers\ResultController@teacher_percentage');
		// Route::post('teacher_examlist','OmrControllers\ResultController@teacher_examlist');
		Route::post('teacher_studentlist','OmrControllers\ResultController@teacher_studentlist');
		Route::post('sectionlist','OmrControllers\ResultController@sectionlist');
		Route::post('notifications','OmrControllers\ResultController@notifications');
		/*OMR*/
		Route::get('groups/{subject_id}','OmrControllers\ResultController1@groups');
		Route::get('groups','OmrControllers\ResultController1@groups');
		Route::post('search','OmrControllers\ResultController1@search');
		Route::post('md_studentlist','OmrControllers\ResultController1@md_studentlist');
		Route::post('md_employeelist','OmrControllers\ResultController1@md_employeelist');
		// Route::get('filter','OmrControllers\BaseController@groups');
		Route::get('class_years/{group_id}','OmrControllers\ResultController1@class_year_wrt_group');
		Route::get('streams/{group_id}/{class_id}','OmrControllers\ResultController1@stream_wrt_group_class_year');
		Route::get('programs/{stream_id}/{class_id}','OmrControllers\ResultController1@programs_wrt_stream_class_year');
		Route::post('getExamData', 'AuthController@tokenAuthCheck'); 
		Route::post('uploadTemplate','AuthController@templateData');
		Route::post('deleteTemplate','AuthController@templateDelete');
		Route::post('getTemplates','AuthController@gettemplateData');
		Route::post('getTemplateData','AuthController@templatedataDownload');
		Route::post('getUpdatedplaystoreurl','AuthController@getUpdatedplaystoreurl');
	});

