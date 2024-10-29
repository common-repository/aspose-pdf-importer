<?php

/*
 * Including the Aspose.Words Cloud PHP SDK
 */

require __DIR__.'/vendor/autoload.php';

use Aspose\Words\WordsApi;
use Aspose\Words\Model;
use Aspose\Words\Model\SaveOptionsData;
use Aspose\Words\Model\Requests;

/*
 *  Assign appSID and appKey of your Aspose App
 */
$appSID = $_REQUEST['appSID'];
$appKey = $_REQUEST['appKey'];
$baseProductUri = 'https://api.aspose.cloud';

$filename = $_REQUEST['filename'];
// IA: Extracting the Path of the file URL
$fileurl = parse_url($_REQUEST['fileurl'])['path'];
// IA: Extracting the dir of the file
$fileurl = dirname($fileurl);
$fileurl = substr($fileurl, strpos($fileurl, "/wp-content/"), strlen($fileurl)-strpos($fileurl, "/wp-content/"));

$ext = pathinfo($filename, PATHINFO_EXTENSION);


if($ext == 'pdf') { 

    $uploadpath = $_REQUEST['uploadpath'];
    $uploadURI = $_REQUEST['uploadURI'];
	$pluginName = $_REQUEST['pluginname'];
	$pluginVersion = $_REQUEST['pluginversion'];	
	$serverPath = substr($uploadpath, 0, strpos($uploadpath, "/wp-content/"));
	$uploadpath =  $serverPath . $fileurl;
    $uploadpath = str_replace('/','\\',$uploadpath);
    $uploadpath = $uploadpath . '\\';
    $pass_upload_path = $uploadpath;

    // Create WordsApi instance    
    $wordsApi = new WordsApi($appSID, $appKey, $baseProductUri);
	// Setting the UserAgent

	
	global $wp_version;
	$wordsApi->getConfig()->setUserAgent(sprintf("%s/%s/ WordPress/$wp_version PHP/%s", $pluginName, $pluginVersion, PHP_VERSION ));

    // Create request and execute api method
    
        $request = new Requests\ConvertDocumentRequest($uploadpath . $filename, "html");
        $content = $wordsApi->convertDocument($request);
    
		$callback = function($match) use ($pass_upload_path,$uploadURI){

        $img_src = $match[2];
        $ext = $match[1];

        $data = base64_decode($img_src);

        $file_name = uniqid().'_img.'.$ext;
        $pass_upload_path = str_replace("\\","/",$pass_upload_path);
        $file = $pass_upload_path . $file_name;


        file_put_contents($file, $data);
        return 'src="' . $uploadURI . '/' . $file_name . '"';

    };

    $content = preg_replace_callback('/src="data:image\/([^;]+);base64,([^"]+)"/i',$callback,$content);

    $content = Remove_Inline_styling($content);
    echo $content;
	
} else {
    echo "Wrong File was selected!";
}



function Remove_Inline_styling($string){

    $string = preg_replace('#<html><head>[\s\S]*?<body>#', '', $string); //remove head tag
    $string = preg_replace('#</body>[\s\S]*?</html>#', '', $string); //remove head tag
    $string = preg_replace('/ style=("|\')(.*?)("|\')/','',$string);
    $string = preg_replace('#<span>[0-9]<\/span>#i', '', $string);
    $string = preg_replace('#<span[^>]*>([\s\S]*?)<\/span[^>]*>#i', '$1', $string);
    $string = preg_replace('#<div[^>]*>([\s\S]*?)<\/div[^>]*>#i', '$1', $string);
    $string = preg_replace("#<p[^>]*>[\s|&nbsp;]*<\/p>#", '', $string);
    $string = gc_remove_p_tags_around_images($string);

    return $string;
}

function gc_remove_p_tags_around_images($content)
{
    $contentWithFixedPTags = preg_replace_callback('/<p>((?:.(?!p>))*?)(<a[^>]*>)?\s*(<img[^>]+>)(<\/a>)?(.*?)<\/p>/is', function ($matches) {
        // image and (optional) link: <a ...><img ...></a>
        $image = $matches[2] . $matches[3] . $matches[4];
        // content before and after image. wrap in <p> unless it's empty
        $content = trim($matches[1] . $matches[5]);
        if ($content) {
            $content = '<p>' . $content . '</p>';
        }
        return $image . $content;
    }, $content);

    // On large strings, this regular expression fails to execute, returning NULL
    return is_null($contentWithFixedPTags) ? $content : $contentWithFixedPTags;
}