@extends('layouts.app')

@section('content')

   
    <div class="container" style="margin-top: 100px">
       <div class="card text-center">
          <div class="card-header">
             <h3>Audio Transcription Result for <i>{{$original_name}}</i></h3>
          </div>
          <div class="card-body">
            <div class="result">
               {{ $result}}
            </div>
                
          </div>
          <div class="card-footer text-muted">
          <i>Transcription time: {{(int)$conversion_time/60 .'mins '. $conversion_time % 60 .'seconds'}}</i>
          </div>
        </div>
    </div>

@stop