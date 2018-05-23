<?php

/**
 * @file
 * Create derivatives for video content.
 */

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\file\Entity\File;

use Drupal\islandora\MimeDetect;

/**
 * This function will create an MP4 file for upload.
 *
 * @param AbstractObject $object
 *   An AbstractObject representing an object within Fedora.
 * @param bool $force
 *   Whether derivative generation is being forced or not.
 *
 * @return array
 *   An array describing the outcome of the MP4 creation.
 *
 * @see hook_islandora_derivative()
 */
function islandora_video_create_mp4(AbstractObject $object, $force = FALSE) {
  if ($force || !isset($object['MP4'])) {
    $archival_file = islandora_video_copy_obj_datastream($object);
    if ($archival_file === FALSE) {
      return islandora_video_no_obj_datastream($object->id);
    }
    $archival_path = \Drupal::service("file_system")->realpath($archival_file['file']->getFileUri());
    $out_file = $archival_path . '.mp4';
    $audio_codec = \Drupal::config('islandora_video.settings')->get('islandora_video_mp4_audio_codec');

    $ffmpeg_executable = \Drupal::config('islandora_video.settings')->get('islandora_video_ffmpeg_path');
    $command = "$ffmpeg_executable -i $archival_path -f mp4 -vcodec libx264 -preset medium -acodec $audio_codec -strict -2 -ab 128k -ac 2 -async 1 -movflags faststart $out_file";
    $return_value = FALSE;
    exec($command, $output, $return_value);
    $archival_file['file']->delete();
    if ($return_value === 0) {
      return islandora_video_add_datastream($object, 'MP4', $out_file);
    }
    else {
      return [
        'success' => FALSE,
        'messages' => [
          [
            'message' => t('Ffmpeg encoding failed for object @pid - MP4 creation was skipped.'),
            'message_sub' => ['@pid' => $object->id],
            'type' => 'watchdog',
            'severity' => RfcLogLevel::ERROR,
          ],
        ],
      ];
    }
  }
}

/**
 * This function will create an OGG file for upload.
 *
 * @param AbstractObject $object
 *   An AbstractObject representing an object within Fedora.
 * @param bool $force
 *   Whether derivative generation is being forced or not.
 *
 * @return array
 *   An array describing the outcome of the OGG creation.
 *
 * @see hook_islandora_derivative()
 */
function islandora_video_create_ogg(AbstractObject $object, $force = FALSE) {
  if ($force || !isset($object['OGG'])) {
    $archival_file = islandora_video_copy_obj_datastream($object);
    if ($archival_file === FALSE) {
      return islandora_video_no_obj_datastream($object->id);
    }
    $archival_path = \Drupal::service("file_system")->realpath($archival_file['file']->getFileUri());
    $out_file = $archival_path . '.ogg';

    $theora_executable = \Drupal::config('islandora_video.settings')->get('islandora_video_ffmpeg2theora_path');
    $command = "$theora_executable $archival_path -o $out_file";
    $return_value = FALSE;
    exec($command, $output, $return_value);
    $archival_file['file']->delete();
    if ($return_value === 0) {
      return islandora_video_add_datastream($object, 'OGG', $out_file);
    }
    else {
      return [
        'success' => FALSE,
        'messages' => [
          [
            'message' => t('Ffmpeg2theora encoding failed for object @pid - OGG creation was skipped.'),
            'message_sub' => ['@pid' => $object->id],
            'type' => 'watchdog',
            'severity' => RfcLogLevel::ERROR,
          ],
        ],
      ];
    }
  }
}

/**
 * This function will create an MKV file for upload.
 *
 * @param AbstractObject $object
 *   An AbstractObject representing an object within Fedora.
 * @param bool $force
 *   Whether derivative generation is being forced or not.
 *
 * @return array
 *   An array describing the outcome of the MKV creation.
 *
 * @see hook_islandora_derivative()
 */
function islandora_video_create_mkv(AbstractObject $object, $force = FALSE) {
  if ($force || !isset($object['MKV'])) {
    $archival_file = islandora_video_copy_obj_datastream($object);
    if ($archival_file === FALSE) {
      return islandora_video_no_obj_datastream($object->id);
    }
    $archival_path = \Drupal::service("file_system")->realpath($archival_file['file']->getFileUri());
    $out_file = $archival_path . '.mkv';

    $ffmpeg_executable = \Drupal::config('islandora_video.settings')->get('islandora_video_ffmpeg_path');
    $command = "$ffmpeg_executable -i $archival_path $out_file";
    $return_value = FALSE;
    exec($command, $output, $return_value);
    $archival_file['file']->delete();
    if ($return_value === 0) {
      return islandora_video_add_datastream($object, 'MKV', $out_file);
    }
    else {
      return [
        'success' => FALSE,
        'messages' => [
          [
            'message' => t('Ffmpeg encoding failed for object @pid - MKV creation was skipped.'),
            'message_sub' => ['@pid' => $object->id],
            'type' => 'watchdog',
            'severity' => RfcLogLevel::ERROR,
          ],
        ],
      ];
    }
  }
}

/**
 * Create the thumbnail for the given file.
 *
 * @param AbstractObject $object
 *   An AbstractObject representing an object within Fedora.
 * @param bool $force
 *   Whether derivative generation is being forced or not.
 *
 * @return array
 *   An array describing the outcome of the TN creation.
 *
 * @see hook_islandora_derivative()
 */
function islandora_video_create_thumbnail(AbstractObject $object, $force = FALSE) {
  if ($force || !isset($object['TN'])) {
    $archival_file = islandora_video_copy_obj_datastream($object);
    if ($archival_file === FALSE) {
      return islandora_video_no_obj_datastream($object->id);
    }
    $archival_path = \Drupal::service("file_system")->realpath($archival_file['file']->getFileUri());
    $out_file = $archival_path . '-TN.jpg';
    $ffmpeg_executable = \Drupal::config('islandora_video.settings')->get('islandora_video_ffmpeg_path');

    $vid_length = "$ffmpeg_executable -i $archival_path 2>&1";
    exec($vid_length, $time_output, $ret_value);

    $dur_match = FALSE;
    $duration = '';
    foreach ($time_output as $value) {
      preg_match('/Duration: (.*), start/', $value, $time_match);
      if (count($time_match)) {
        $dur_match = TRUE;
        $duration = $time_match[1];
        break;
      }
    }
    if ($dur_match) {
      // Snip off the ms because we don't care about them.
      $time_val = preg_replace('/\.(.*)/', '', $duration);
      $time_array = explode(':', $time_val);
      // Calculate the number of seconds halfway through the video.
      $output_time = floor((($time_array[0] * 360) + ($time_array[1] * 60) + $time_array[2]) / 2);

      $tn_creation = "$ffmpeg_executable -itsoffset -2 -ss $output_time -i $archival_path -vcodec mjpeg -vframes 1 -an -f rawvideo $out_file";

      $return_value = FALSE;
      exec($tn_creation, $output, $return_value);
      $archival_file['file']->delete();
      if ($return_value === 0) {
        return islandora_video_add_datastream($object, 'TN', $out_file);
      }
      // Unable to generate with Ffmpeg, add default TN.
      else {
        $stored_thumbnail = drupal_get_path('module', 'islandora_video') . '/images/crystal_clear_app_camera.png';
        return islandora_video_add_datastream($object, 'TN', $stored_thumbnail, FALSE);
      }
    }
    // Unable to grab duration at the default thunbnail.
    else {
      $archival_file['file']->delete();
      $stored_thumbnail = drupal_get_path('module', 'islandora_video') . '/images/crystal_clear_app_camera.png';
      return islandora_video_add_datastream($object, 'TN', $stored_thumbnail, FALSE);
    }
  }
}

/**
 * Adds the file as a datastream to the given object.
 *
 * @param AbstractObject $object
 *   The object to add the datasteam to.
 * @param string $datastream_id
 *   The datastream id of the added datastream.
 * @param string $file_uri
 *   A URI to the file containing the content for the datastream.
 * @param bool $delete_file
 *   A flag indicating if the file should be deleted after ingest.
 *
 * @return array
 *   An array describing the outcome of the PROXY_MP3 creation.
 *
 * @see hook_islandora_derivative()
 */
function islandora_video_add_datastream(AbstractObject $object, $datastream_id, $file_uri, $delete_file = TRUE) {
  try {
    $ingest = !isset($object[$datastream_id]);
    $mime_detector = new MimeDetect();

    if ($ingest) {
      $ds = $object->constructDatastream($datastream_id, "M");
      $ds->label = $datastream_id;
    }
    else {
      $ds = $object[$datastream_id];
    }
    if ($ds->mimetype != $mime_detector->getMimetype($file_uri)) {
      $ds->mimetype = $mime_detector->getMimetype($file_uri);
    }
    $ds->setContentFromFile($file_uri);
    if ($ingest) {
      $object->ingestDatastream($ds);
    }
    if ($delete_file && file_exists($file_uri)) {
      file_unmanaged_delete($file_uri);
    }
    return [
      'success' => TRUE,
      'messages' => [
        [
          'message' => t('Created @dsid derivative for OBJ (@pid).'),
          'message_sub' => [
            '@dsid' => $datastream_id,
            '@pid' => $object->id,
          ],
          'type' => 'dsm',
        ],
      ],
    ];
  }
  catch (exception $e) {
    return [
      'success' => FALSE,
      'messages' => [
        [
          'message' => t('Failed to add @dsid datastream for @pid. Error message: "@message" with trace: @stack'),
          'message_sub' => [
            '@dsid' => $datastream_id,
            '@pid' => $object->id,
            '@message' => $e->getMessage(),
            '@stack' => $e->getTraceAsString(),
          ],
          'type' => 'watchdog',
          'severity' => RfcLogLevel::ERROR,
        ],
      ],
    ];
  }
}

/**
 * Copies the OBJ to the file system.
 *
 * @param AbstractObject $object
 *   An AbstractObject representing an object within Fedora.
 *
 * @return array|object
 *   FALSE when the object doesn't have the OBJ, an array otherwise.
 */
function islandora_video_copy_obj_datastream(AbstractObject $object) {
  $mime_detect = new MimeDetect();
  if (!isset($object['OBJ'])) {
    return FALSE;
  }
  $ext = $mime_detect->getExtension($object['OBJ']->mimeType);
  $file_name = str_replace(':', '-', $object->id);

  // Create a file object we can save.
  $file_uri = file_create_filename("{$file_name}OBJ.{$ext}", 'temporary://');
  $image = File::create();
  $image->setFileUri($file_uri);
  $image->setFilename($file_name);
  $image->setOwnerId(\Drupal::currentUser()->id());
  $image->setMimeType($object['OBJ']->mimeType);
  $object['OBJ']->getContent($file_uri);
  $image->save();

  return [
    'file' => $image,
    'extension' => $ext,
    'filename' => $file_name,
  ];
}

/**
 * Helper function that returns a message when no OBJ datastream exists.
 *
 * @param string $pid
 *   The PID of the object that derivatives are being generated on.
 *
 * @return array
 *   An array describing the failure of the operation.
 *
 * @see hook_islandora_derivative()
 */
function islandora_video_no_obj_datastream($pid) {
  return [
    'success' => FALSE,
    'messages' => [
      [
        'message' => t('Could not create video derivatives for %s. No video file was uploaded.'),
        'message_sub' => ['%s' => $pid],
        'type' => 'watchdog',
        'severity' => RfcLogLevel::ERROR,
      ],
    ],
  ];
}
