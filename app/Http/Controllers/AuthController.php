<?php 
namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Employee;
use \App\BaseModels\Campus;
use \App\OmrModels\Version;
use App\Token;
use \App\OmrModels\Exam;
use \App\OmrModels\Subject;
use \App\OmrModels\Modesyear;
use \App\OmrModels\Mode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Http\Resources\ExamCollection;
use App\Http\Resources\TemplateCollection;
use Illuminate\Support\Facades\Hash;
use File;
use Mail;

use App\Http\Resources\Employee as UserResource;

class AuthController extends Controller
{
	public function tokenAuthCheck (Request $request) {
		
		// return Auth::id();
		$msg="This is old token";
		if(Auth::id()){
			//After auth from token find role of the user
			  $role=DB::table('roles')
                  ->join('user_roles','roles.roll_id','=','user_roles.ROLL_ID')
                  ->join('employees','employees.payroll_id','=','user_roles.payroll_id')
                  ->where('employees.id','=',Auth::id())
                  ->select('roles.role')
                  ->get();
			for($i=0;$i<=sizeof($role)-1;$i++){
				$rolearray[]=$role[$i]->role;
			}
			//Get Token from user id for expired or not reason 
			$token=Token::whereUser_id(Auth::id())->get();
			if($role[0]->role!='EXAM_ADMIN'){
$camp=DB::table('t_campus')->select('state_id')->where('CAMPUS_ID',Auth::user()->CAMPUS_ID)->get();

$exam=new ExamCollection(
						Exam::
						select('*')
                              ->whereRaw('FIND_IN_SET('.$camp[0]->state_id.',1_exam_admin_create_exam.state_id)')
                              ->distinct()
                              ->get()
                          );

			}
			else{
				 $exam=new ExamCollection(
				 		Exam::		 		
						select('*')
                              ->whereIn('1_exam_admin_create_exam.state_id',function($query){
                                $query->select('1_exam_admin_create_exam.state_id')
                                ->from('t_campus as c','t_employee as e')
                                ;
                                })
                               ->distinct()
                              ->paginate());
			}
			$campus=Campus::select('CAMPUS_NAME','CAMPUS_ID')->where('CAMPUS_ID','=',Auth::user()->CAMPUS_ID)->get();
			$client = Employee::find(Auth::id());
		        $uc=$client->tokens()->where('created_at', '<', Carbon::now()->subDay())->delete();
		   if($uc){
		   	$msg='Token expired and New Token generated';
		   }
			if (!$token->count()) {
				$str=str_random(10);
				$token=Token::create([
					'user_id'=>Auth::id(),
					'expiry_time'=>'1',
			        'access_token' => Hash::make($str),
				]);
				 return [
						'Login' => [
							'response_message'=>"success",
							'response_code'=>"1",
							],
						'Exam'=>$exam,
					];
			 
			}
				return [
						'Login' => [
							'response_message'=>"success",
							'response_code'=>"1",
							],
						'Exam'=>$exam,
					];
			}
		
	} 

	public function templateDelete (Request $request) {
		
		$templatedelete=Mode::deletetemplate($request->omr_scanning_type,$request->model_years,$request->test_mode_id);
		

		return $templatedelete;

	}
public function gettemplateData (Request $request) {
	
	$mode=Modesyear::join('0_test_modes','0_test_modes.test_mode_type','=','0_test_modes_years.test_mode_type')
	->select('0_test_modes_years.model_years'
,
DB::raw('CASE WHEN 0_test_modes_years.template_data != "" THEN "true" ELSE "false" END AS template_uploaded_status')
,'0_test_modes.test_mode_subjects')
			->where('0_test_modes_years.test_mode_type','1')
			->orWhere('0_test_modes_years.test_mode_type','2')
			->orderby('0_test_modes_years.model_years')
			->get();

$subject=Subject::all();
	$modedata=Mode::select('test_mode_id','test_mode_name','bit',
DB::raw('CASE WHEN template_data != "" THEN "true" ELSE "false" END AS template_uploaded_status')
,'test_mode_subjects')
			->where('adv1_nonadv0','0')
			->whereRaw('test_mode_name!=""')
			->get();

		return [
			'Login' => [
							'response_message'=>"success",
							'response_code'=>"1",
							],
			'Advanced'=>$mode,
			'Non_Advanced'=>$modedata,
			'Subjects'=>$subject,

			];

	}

	public function templateData (Request $request) {
		//Decode the json format
			$manage = (array) json_decode($request->template_data);
		//Encode the decoded json
			$manage1=json_encode($manage);
			//Validate
			 if(!$request->template_data){
			 		return [
					'Login' => [
								'response_message'=>"Template Object is required",
								'response_code'=>"0"],
					];
			   }
			 $data = json_decode($request->template_data, true);
			  if(!$request->hasFile('images')){
			 		return [
					'Login' => [
								'response_message'=>"Template Image is required",
								'response_code'=>"0"],
					];
			   }
			 if(!$request->omr_scanning_type){
			 		return [
					'Login' => [
								'response_message'=>"omr_scanning_type is required",
								'response_code'=>"0"],
					];
			 }
             

			 if($request->omr_scanning_type=='Advanced'){
			 	if(!$request->model_years){
			 		return [
					'Login' => [
								'response_message'=>"model_years is required",
								'response_code'=>"0"],
					];
			   }
		  
		 	 if ($request->hasFile('images')) 
		      {
		      	//Increase the file accept size of the server
		        ini_set('memory_limit','256M');
		        $file = $request->file('images');
		        //Change image name with time and given model_years
		        $input=time().'_'.trim($request->model_years, '"').'.'.$file->getClientOriginalExtension();
		        $path=public_path()."/images";	        
		        $request->file('images')->move($path, $input);
		   	 }
		   	 try{
			   	 		$mode=Modesyear::where('model_years',trim($request->model_years, '"'))
			          		->update(['template_data' =>  $request->template_data,'template_path' => "https://103.206.115.37/omr/public/images/".$input]);
				        $modedata=Modesyear::where('model_years',trim($request->model_years, '"'))
				          ->select('template_data')->get();


				return [
						'Login' => [
									'response_message'=>"success",
									'response_code'=>"1"
								],
				];

		   	 }catch(Exception $e){

			   	 	return [
							'Login' => [
										'response_message'=>$e->errorMessage(),
										'response_code'=>"0"],
					];

		   	 }
				
         
		}
		elseif($request->omr_scanning_type=='Non-Advanced'){
			 if ($request->hasFile('images')) 
			    {
			      	if(!$request->test_mode_id){
				 		return [
						'Login' => [
									'response_message'=>"test_mode_id is required",
									'response_code'=>"0"],
						];
				   }
				   //Increase the file accept size of the server
			        ini_set('memory_limit','256M');
			        $file = $request->file('images');
			        $input=time().'_'.trim($request->test_mode_id, '"').'.'.$file->getClientOriginalExtension();
			        $path=public_path()."/images";	        
			        $request->file('images')->move($path, $input);
			    }
			
			$mode=Mode::where('test_mode_id',trim($request->test_mode_id, '"'))
          ->update(['template_data' =>  $request->template_data,'template_path' =>"https://103.206.115.37/omr/public/images/".$input]);

          $modedata=Mode::where('test_mode_id',trim($request->test_mode_id, '"'))->select('template_data')
          ->get();
         
			return [
				'Login' => [
							'response_message'=>"success",
							'response_code'=>"1"],
		];
		}
		else{
				return [
				'Login' => [
							'response_message'=>$request->omr_scanning_type,
							'response_code'=>"1"],
		];
		}
	}

	public function templatedataDownload (Request $request) 
	{
		   $modedata=Modesyear::select('template_data','model_years')
		   	->where('template_data', '<>', '', 'and')->get();  
		   $modedata2=Mode::select('template_data','test_mode_id')
		   	->where('template_data', '<>', '', 'and')->get();

			return [

				'Login' => [
							'response_message'=>"success",
							'response_code'=>"1"],
				'Template_Advanced'=>$modedata,
				'Template_Non_Advanced'=>$modedata2,
		];
	}
	public function tokenAuthAttempt (Request $request) {
		$msg="This is old token";
		//Find Employee using payroll_id and password
		Auth::attempt([ 'payroll_id' => $request->get('payroll_id'), 'password' => $request->get('password') ]);
		//Check version of the app
		$version=Version::orderby('version_number','DESC')->first();
		if(Auth::id()){
			//Fetch role from role table using Auth id
			   $role=DB::table('roles')
                  ->join('user_roles','roles.roll_id','=','user_roles.ROLL_ID')
                  ->join('employees','employees.payroll_id','=','user_roles.payroll_id')
                  ->where('employees.id','=',Auth::id())
                  ->select('roles.role')
                  ->get();
			for($i=0;$i<=sizeof($role)-1;$i++){
				$rolearray[]=$role[$i]->role;
			}

			$token=Token::whereUser_id(Auth::id())->get();
			//From Exam Collection details for exam list(EXAM_ADMIN)
			if($role[0]->role!='EXAM_ADMIN'){
			  $exam= new ExamCollection(Exam::select('*')
                              ->whereIn('state_id',function($query){
                                $query->select('state_id')
                                ->from('t_campus as c','t_employee as e')
                                ->whereRaw('campus_id = 54');
                                })->paginate());
			}
			else{
			//From Exam Collection details for exam list(COMPUTER OPERATOR)
				 $exam= new ExamCollection(Exam::select('*')
                              ->whereIn('state_id',function($query){
                                $query->select('state_id')
                                ->from('t_campus as c','t_employee as e')
                                ;
                                })->paginate());
			}
			//Get CAMPUS DETAILS
			$campus=Campus::select('CAMPUS_NAME','CAMPUS_ID')->where('CAMPUS_ID','=',Auth::user()->CAMPUS_ID)->get();
			//Fetch Employee details and token using id
			$client = Employee::find(Auth::id());
		        $uc=$client->tokens()->where('created_at', '<', Carbon::now()->subDay())->delete();
		   if($uc){
		   	$msg='Token expired and New Token generated';
		   }
			if (!$token->count()) {
				$str=str_random(10);
				//Create Token for logged user
				$token=Token::create([
					'user_id'=>Auth::id(),
					'expiry_time'=>'1',
			        'access_token' => Hash::make($str),
				]);
			return [
						'Login' => [
							'response_message'=>"success",
							'response_code'=>"1",
							'token'=>$token->access_token,
							'Role'=>
							$rolearray
							,
							'CAMPUS_NAME'=>$campus[0]['CAMPUS_NAME'],
							'CAMPUS_ID'=>$campus[0]['CAMPUS_ID'],
							'Designation'=>Auth::user()->DESIGNATION,
							'Version'=>$version->version_number,
							],
					];
			 
			}
				return [
						'Login' => [
							'response_message'=>"success",
							'response_code'=>"1",
							'token'=>$token[0]['access_token'],
							'Role'=>
							$rolearray
							,
							'CAMPUS_NAME'=>$campus[0]['CAMPUS_NAME'],
							'CAMPUS_ID'=>$campus[0]['CAMPUS_ID'],
							'Designation'=>Auth::user()->DESIGNATION,
							'Version'=>$version->version_number,
							],
					];
			}
			else{
				return [
						'Login' => [
							'response_message'=>"payroll_id or password wrong",
							'response_code'=>"0"],
					];
			}
		
	}

	

	public function upload (Request $request) {
		if(!$request->Exam_Id){
			return [
				'Login' => [
							'response_message'=>"Exam_Id required",
							'response_code'=>"0"],
		];
		}
		if(!$request->Campus_Id){
			return [
				'Login' => [
							'response_message'=>"Campus_Id required",
							'response_code'=>"0"],
		];
		}
		$CAMPUS_NAME=Campus::select('CAMPUS_ID','CAMPUS_NAME')->where('CAMPUS_ID','=',$request->Campus_Id)->get();

		 if ($request->hasFile('files')) 
	      {
	      	//Increase the file accept size of the server
	        ini_set('memory_limit','256M');
	        $file = $request->file('files');
	        //Size of the file
	        $size = $request->file('files')->getClientSize();
	        //Get Extension of the file
	        $check=$file->getClientOriginalExtension();

	        if($check=='dat' || $check=='iit')
	        {
	        $input=$CAMPUS_NAME[0]['CAMPUS_NAME'].'_'.$request->Exam_Id.'.'.$file->getClientOriginalExtension();
	        $input1='temp_'.$CAMPUS_NAME[0]['CAMPUS_ID'].'.'.$file->getClientOriginalExtension();
	        $path='/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads'.'/'.$request->Exam_Id;
	        //Check the path is present otherwise create new one(temp)
	        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
	        //Check the path is present otherwise create new one(permanent)
	        File::isDirectory($path.'/first') or File::makeDirectory($path.'/first', 0777, true, true);
	        //move the file to that path 
	        $request->file('files')->move($path.'/first', $input);
	        //Copy the file which is in first in previous path
	        $success = File::copy($path.'/first/'.$input,$path.'/'.$input);
	        //Rename the copied file 
	        $success = File::move($path.'/'.$input,$path.'/'.$input1);
	        //add campus_id with comma seperated value in is_college_id_mobile_uploaded
	    	$isupload=Exam::where('sl',$request->Exam_Id)
          		->update(['is_college_id_mobile_uploaded' => DB::raw("CONCAT(coalesce(is_college_id_mobile_uploaded,''),',','".$request->Campus_Id."')")
          	] );

	            return [
	            	'Login' => [
							'response_message'=>"success",
							'response_code'=>"1",
							'Image_Uploaded'=> '/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads'.'/'.$input,
							'size'=>$size
							],
	                            
	                            
	                        ];

	                    }
	                    else{
	                        return [
				'Login' => [
							'response_message'=>".dat or .iit files are acceptable",
							'response_code'=>"0"],
		];
	                    }
	        }
	        else{
	            return [
				'Login' => [
							'response_message'=>"files required",
							'response_code'=>"0"],
		];
	        }
	}
	public function getUpdatedplaystoreurl(){
		//Check the PlayStore_URL of the app from version table
		$purl=DB::table('App_version')
				->select('playstore_url')
				->orderby('version_number','DESC')
				->limit(1)
				->get();
				return [
				'Login' => [
							'response_message'=>"success",
							'response_code'=>"1",
				'Playstore_url'=>$purl[0]->playstore_url,
						],
		];
	}
	public function check(){
  

	 

	Mail::send('welcome', array('body' => 'email_body'), 

	    function($message){

	        $message->from('muthumaharajan1992@gmail.com', 'Administrator')

	        ->subject('Admin Subject')->to('muthumaharajan1992@gmail.com','Muthu Test');

	});
	}
}
