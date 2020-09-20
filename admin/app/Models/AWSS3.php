<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \AWS;

class AWSS3 extends Model
{

    public static function updateS3($fileKey, $sourceFile, $bucket) {

        $s3 = AWS::createClient('s3');
        $r = $s3->putObject(array(
            'Bucket'     => $bucket,
            'Key'        => $fileKey,
            'SourceFile' => $sourceFile,
        ));

        return $r;

    }


    public static function saveJSONToS3($itemsArr, $filename, $bucket = '') {

        if (empty($bucket)) {
            $bucket = $bucket = \App\Site::inst('AWS_BUCKET');
        }
        $itemsJson = json_encode($itemsArr);
        $path = "/tmp/";
        echo '<pre>';
        echo (json_encode($itemsArr, JSON_PRETTY_PRINT));
        echo '</pre>';
        $r = file_put_contents($path . $filename, $itemsJson);
        if (!$r) {
            \Log::error("Failed to save json to $path$filename");
        } else {
            $aws = new AWSS3();
            $r = $aws->updateS3('json/' . $filename, $path . $filename, $bucket);
            if ($r) {
                echo "$filename uploaded to s3<br><br>";
            } else {

            }
        }

    }

}