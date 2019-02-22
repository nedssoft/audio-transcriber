@extends('layouts.app')

@section('content')

   
    <div class="container" style="margin-top: 100px">
       <div class="card text-center">
          <div class="card-header">
             <h1>Audio Transcription</h1>
          </div>
          <div class="card-body">
            <h5 class="card-title">Upload the file to be transcribed below</h5>
            <img src="img/loader.gif" class="loader" style="display: none; margin-top: 50px;">
                    <div class="content">     
                        <div class="col-md-6 offset-md-2" style="margin-top: 50px">
                             <form enctype="multipart/form-data" method="post" action="{{ route('upload.audio')}}" id="form">
                                @csrf
                              <div class="form-row align-items-center">
                                
                               
                                <div class="col-auto">
                                  <div class="form-group">
                                    
                                    <input type="file" accept="audio/*" name="audio" id="file">
                                      
                                  </div>
                                </div>

                                <div class="col-auto">
                                  <button type="submit" class="btn btn-primary mb-2">Transcribe</button>
                                </div>

                              </div>
                            </form>
                        </div>
                    </div>
          </div>
          <div class="card-footer text-muted">
        
          </div>
        </div>
    </div>
@push('scripts')
    <script>
        $(document).ready(function(){
           
            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
            });
            $('#form').submit(function(e){

                e.preventDefault();
                $('.loader').show();
                $('.card-title').text(
                    'Transcribing... This may take some time depending on the size of the audio').css('color', 'black'
                );
                let token = $("[name=csrf-token]").attr('content');
                let form = document.getElementById('form');
                let formData = new FormData(form);
                $.ajax({
                    url: "{{ route('upload.audio')}}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    enctype: 'multipart/form-data',
                    success: function(res){
                        console.log(res);
                        if (res.status == 'success') {
                            $('.loader').remove();
                            $('.card-title').text('Transcription result for '+ res.original_name);
                            $('.content').html('<p>' + res.result + '</p>');
                            $('card-footer').html('<i> Transcription time: '+ (res.conversion_time/60) +'mins '+ (res.conversion_time %60) +'secs ' + '</i>'
                             );
                        }
                        else if(res.status == 'error'){
                             $('.loader').remove();
                             $('.card-title').text(res.message).css('color', 'red');
                        }
                    },
                    error: function(err){
                         $('.loader').remove();
                        console.log(err);
                         if (err.status == 500) {
                             $('.card-title').text(err.statusText).css('color', 'red');
                         }
                        else{

                            let error = JSON.parse(err.responseText).errors.audio[0];
                       
                            $('.card-title').text(error).css('color', 'red');
                        }
                    }
                });
            });
        });
    </script>
@endpush
@stop