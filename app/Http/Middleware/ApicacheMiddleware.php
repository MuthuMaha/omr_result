<?php

namespace App\Http\Middleware;
use App\Apicache;
use Closure;
use Auth;
class ApicacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
         $column=\Request::segment(2);
        switch ($column) 
        {
            case 'total_percentage': case 'md_total_percentage':
              if($request->user_type=='student' || $request->user_type=='parent'){
                    $un=Auth::user()->ADM_NO;
                $data=Apicache::where(['USERNAME'=>$un,
                    'user_type'=>$request->user_type])->pluck('total_percentage');
              }
                elseif($request->user_type=='employee' && $column=='total_percentage'){
                    $un=Auth::user()->PAYROLL_ID;
                $data=Apicache::where(['USERNAME'=>$un,
                    'user_type'=>$request->user_type, 'group_id'=>$request->group_id,
            'class_id'=>$request->class_id,
            'stream_id'=>$request->stream_id,
            'program_id'=>$request->program_id,
            'subject_id'=>$request->subject_id])->pluck('total_percentage');
                }
                else{
                    $un=$request->USER_ID;
                $data=Apicache::where(['USERNAME'=>$un,
                    'user_type'=>$request->user_type, 'group_id'=>$request->group_id,
            'class_id'=>$request->class_id,
            'stream_id'=>$request->stream_id,
            'program_id'=>$request->program_id,
            'subject_id'=>$request->subject_id])->pluck('total_percentage');
                }

                if(isset($data[0])){
                    $d=json_encode($data[0]);
                return response(json_decode($d),200);
                }
                else{
             return $next($request);

                }
                break;

            //     case 'examlist':
            //      if($request->user_type=='student' || $request->user_type=='parent'){
            //         $un=Auth::user()->ADM_NO;
            //     $data=Apicache::where(['USERNAME'=>$un,
            //         'user_type'=>$request->user_type])->pluck('examlist');
            //   }
            //     elseif($request->user_type=='employee' && $column=='examlist'){
            //         $un=Auth::user()->PAYROLL_ID;
            //     $data=Apicache::where(['USERNAME'=>$un,
            //         'user_type'=>$request->user_type, 'group_id'=>$request->group_id,
            // 'class_id'=>$request->class_id,
            // 'stream_id'=>$request->stream_id,
            // 'program_id'=>$request->program_id,
            // 'subject_id'=>$request->subject_id,
            // 'page'=>$request->page,
            // 'mode_id'=>$request->mode_id,
            // 'test_type'=>$request->test_type,
            // 'date'=>$request->date])->pluck('examlist');
            //     }
            //     else{
            //         $un=$request->USER_ID;
            //     $data=Apicache::where(['USERNAME'=>$un,
            //         'user_type'=>$request->user_type, 'group_id'=>$request->group_id,
            // 'class_id'=>$request->class_id,
            // 'stream_id'=>$request->stream_id,
            // 'program_id'=>$request->program_id,
            // 'subject_id'=>$request->subject_id,
            // 'page'=>$request->page,
            // 'mode_id'=>$request->mode_id,
            // 'test_type'=>$request->test_type,
            // 'date'=>$request->date])->pluck('examlist');
            //     }

            //     if(isset($data[0])){
            //         $d=json_encode($data[0]);
            //     return response(json_decode($d),200);
            //     }
            //     else{
            //        return $next($request);
            //      }
                  
            //     break;
            
            default:
             return $next($request);
                break;
        }
        
       
        
    }
}
