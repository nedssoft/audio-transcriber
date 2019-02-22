<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Speech\SpeechClient;
use RobbieP\CloudConvertLaravel\CloudConvert;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\ExponentialBackoff;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Filesystem\Filesystem;


class TranscriptionController extends Controller
{
    
    public function __construct()
    { 
        $this->path = public_path('audio-contents/');
        $this->apikey = config('cloudconvert.api_key');
        $this->bucket_name = env('GOOGLE_CLOUD_STORAGE_BUCKET', 'my-chatdesk-test');
    }

    /**
     * Initializes the SpeechClient
     * @return object \SpeechClient
     */
    public function createInstance()
    {
        $project_id = env('PROJECT_ID','my-chatdesk-test');
        $speech = new SpeechClient([
            'projectId' => $project_id,
            'languageCode' => 'en-US',
        ]);
        
        
        return $speech;
    }

    /**
     * This method controls other methods to perform the transcription
     * @param  Request $request
     * @return Response Http\Response
     */
    public function convert(Request $request)
    {   

        $start = time();

        $this->validate($request, [
            'audio' => 'required', 'mimeTypes:mp3,flac,wav'
        ], [
            'audio.required' => 'Upload the audio file you want to transcribe'
        ]);

        $result = '';

        $audio = $request->file('audio');

        $filename = $audio->getClientOriginalName();
        $ext      = $audio->getClientOriginalExtension();

        $original_name = $filename;

        $filename = str_slug(trim($filename, ".{$ext}")).'-'.time();

        $object_name = $this->uploadToGoogleCloud($audio, $filename);

        $result = $this->transcribe($object_name);
     
        $conversion_time = time() - $start;

        $this->deleteFile($this->getFilename($filename));

        if (request()->expectsJson()){
            if (! empty($result)) {

            return response()->json([
                'status' => 'success',
                'result' => $result,
                'conversion_time' => $conversion_time,
                'original_name' => $original_name
            ]);
        }
            return response()->json([
                'status' => 'error',
                 'message' => 'Transcriber returned a null result'
            ]);
        }
        return view('result', compact('result', 'conversion_time', 'original_name'));
    }

    /**
     * Rename the file to FLAC format
     * @param  string  $name unique filename
     * @return striing $name
     */
    public static function getFilename($name)
    {
        return $name.".flac";
    } 

    /**
     * This uploads the audio to Google Cloud Storage before transcription
     * Reason is that files longer than 1 min can only be
     * trancribed through this method
     * @param  \http\UploadedFile $audio     the uuploaded audio file
     * @param  string $_filename the file name
     * @return string          the Google cloud object name of the uploaded file
     */
    public function uploadToGoogleCloud($audio, $_filename)
    {   
        $result = '';
 
        /**
         * Since the Google-Speech-To-Text converter used only works well if the audio format
         * is FLAC, we first convert the audio to FLAC fromat using CloudCovert library 
         * @var CloudConvert
         */
        $cloudconvert = new CloudConvert([

            'api_key' => $this->apikey
        ]);

        $filename = self::getFilename($_filename);

        try {

            $cloudconvert->file($audio)->to($this->path . $filename);

        } catch (ClientException $e) {
           
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        $file_path = $this->path.$filename;

         $storage = new StorageClient();

         $bucket = $storage->bucket($this->bucket_name);
        
         $bucket->upload(
            fopen($file_path, 'r')
         );

         return $filename;
        
    }

    /**
     * Performs the actual trancription
     * @param  Google\Cloud\Storage\StorageClient $object_name  
     * @return string             the transcription result
     */
    public function transcribe($object_name)
    {
         $speech = new SpeechClient([
         "languageCode"=>"en-US",
        ]);

        // Fetch the storage object
        $storage = new StorageClient();
        $object = $storage->bucket($this->bucket_name)->object($object_name);

        $options = [
            'encoding' => 'FLAC',
             "languageCode"=>"en-US",
        ];
        // Create the asyncronous recognize operation
        $operation = $speech->beginRecognizeOperation(
            $object, $options
        );

        // Wait for the operation to complete
        $backoff = new ExponentialBackoff(10);
        $backoff->execute(function () use ($operation) {
            
            $operation->reload();
            if (!$operation->isComplete()) {
                throw new \Exception('Job has not yet completed', 500);
            }
        });

        // Print the results
        $result_str = '';

        if ($operation->isComplete()) {
            $results = $operation->results();

            foreach ($results as $result) {

                $result_str.=  $result->alternatives()[0]['transcript'] . PHP_EOL;
            }
           
        }

        return $result_str;
    }

    /**
     * Delete the uploaded file once the transcription is complete
     * @param  string  $filename The file name
     * @return void
     */
    public function deleteFile($filename)
    {
        $file = $this->path.$filename;

        $filesystem = new Filesystem;

        if ($filesystem->exists($file)) {

            $filesystem->delete($file);
        }
        return;
    }
    
}
