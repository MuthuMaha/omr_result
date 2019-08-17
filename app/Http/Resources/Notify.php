<?php

namespace App\Http\Resources;
use App\OmrModels\Type;
use App\OmrModels\Exam;
use Illuminate\Http\Resources\Json\JsonResource;

class Notify extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
     public function toArray($request)
    {
      $result=[
            'title'=>$this->title,
            'test_type'=>Type::where('test_type_id',json_decode($this->parameter)->test_type_id)->pluck('test_type_name')[0],
            'start_date'=>date('d-M-Y',strtotime(Exam::where('sl',json_decode($this->parameter)->exam_id)->pluck('start_date')[0])),
            'url'=>$this->url,
            'parameter'=>json_decode($this->parameter),
            'notify_type'=>$this->notify_type,
        ];
        if($this->notify_type=='Exam Created')
           $result['start_date']=date('d-M-Y',strtotime(Exam::where('sl',json_decode($this->parameter)->exam_id)->pluck('start_date')[0]));
        else
           $result['start_date']=date('d-M-Y h:s:i',strtotime(Exam::where('sl',json_decode($this->parameter)->exam_id)->pluck('rank_generated_date_time')[0]));

        return $result;

    }
}
