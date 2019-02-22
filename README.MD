## Audio Transcriber

This project uses Google-Speech-to-Text to transcribe audio file to readable text

## Usage

### Prerequisites
- Create a Google cloud account
- Generate the API key Json and save in /google-credential/key.json

- Create account on [Cloudconvert](https://cloudconvert.com/api) to generate an API key
- Note, the CloudConvert API is needed for converting the audio file to FLAC before transcription. This is because at the point of doing this project, I was able to get Google-Speech-to-Text work only for .FLAC files.

## Installation
- git clone https://github.com/oriechinedu/audio-transcriber.git 
- cd audio-transcriber
- cp .env.example .env
- Fill the Keys in the .env accordingly with the API keys generated above
- composer install
- php artisan serve

## Tools used
- [Google-Speech-to-Text  API](https://cloud.google.com/speech-to-text/)
- [CloudConvert API](https://cloudconvert.com/api)
- [Google Cloud Storage API](https://cloud.google.com/storage/)

