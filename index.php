<?php
/**
 * Created by PhpStorm.
 * User: manuelmeister
 * Date: 03.04.19
 * Time: 09:59
 */

// includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

// Imports the Cloud Client Library
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

error_reporting( E_ALL );
ini_set( 'error_reporting', - 1 );
ini_set( 'display_errors', 'On' );

$newRequest = true;

// instantiates a client
$client = new TextToSpeechClient();


// build the voice request, select the language code ("en-US") and the ssml
// voice gender
$voice = ( new VoiceSelectionParams() )
	->setLanguageCode( 'de-DE' )
	->setSsmlGender( SsmlVoiceGender::NEUTRAL );

// Effects profile
$effectsProfileId = "telephony-class-application";

// select the type of audio file you want returned
$audioConfig = ( new AudioConfig() )
	->setAudioEncoding( AudioEncoding::MP3 )
	->setEffectsProfileId( array( $effectsProfileId ) );

if ( isset( $_POST['submit'] ) && isset($_POST['daswort']) && $_POST['daswort'] == 'dinimueter1337!' ) {

	$text = 'Nope, try again!';
	if ( isset( $_POST['input'] ) ) {
		$text = ( $_POST['input'] );
	}
	$title = 'Nope, try again!';
	if ( isset( $_POST['title'] ) ) {
		$title = ( $_POST['title'] );
	}

	$synthesisInputText = ( new SynthesisInput() )->setSsml(
		'
	<speak>
	  <par>
	    <media xml:id="jingle" soundLevel="-6db">
	      <audio src="https://42m.ch/jingle.mp3"/>
	    </media>
	    <media begin="jingle.end-2.5s">
	      <speak>Herzlich willkommen zu Radio Fogo!</speak>
	    </media>
	  </par>
	  <speak>' . $text . '</speak>
	  <par>
	    <media xml:id="jingle" soundLevel="-6db">
	      <audio src="https://42m.ch/jingle.mp3"/>
	    </media>
	    <media begin="jingle.end-2.5s">
	      <speak>Das wars! Yeah!</speak>
	    </media>
	  </par>
</speak>
	' );

// perform text-to-speech request on the text input with selected voice
// parameters and audio file type


	$response     = $client->synthesizeSpeech( $synthesisInputText, $voice, $audioConfig );
	$audioContent = $response->getAudioContent();

// the response's audioContent is binary
	$time = time();
	mkdir( "output/{$time}" );
	file_put_contents( "output/{$time}/text.txt", $text );
	file_put_contents( "output/{$time}/title.txt", $title );
	file_put_contents( "output/{$time}/audio.mp3", $audioContent );
}


$directory         = 'output/';
$scanned_directory = array_reverse( array_diff( scandir( $directory ), array( '..', '.' ) ) );

$items = [];
foreach ( $scanned_directory as $item ) {
	$text = file_get_contents( "output/{$item}/text.txt" );
	if(is_file("output/{$item}/title.txt")) {
		$title = file_get_contents( "output/{$item}/title.txt" );
	} else {
		$title = ( new DateTime( "@{$item}" ) )->format( 'Y-m-d H:i' );
	}
	$items[ $item ] = array( 'datetime' => new DateTime( "@{$item}" ), 'audio' => 'audio.mp3', 'title' => $title, 'text' => $text );
}

$loader = new \Twig\Loader\FilesystemLoader( __DIR__ . '/src/view/' );
$twig   = new \Twig\Environment( $loader, [
] );

echo $twig->render( 'index.twig', [ 'audiofiles' => $items ] );
