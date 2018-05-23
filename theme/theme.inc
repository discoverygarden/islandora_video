<?php

/**
 * @file
 * Theme hooks.
 */

use Drupal\Core\Url;

use Drupal\islandora\DublinCore;

/**
 * Implements template_preprocess_HOOK().
 */
function template_preprocess_islandora_video(array &$variables) {
  module_load_include('inc', 'islandora', 'includes/datastream');
  module_load_include('inc', 'islandora', 'includes/utilities');
  module_load_include('inc', 'islandora', 'includes/solution_packs');
  module_load_include('inc', 'islandora', 'includes/metadata');

  $object = $variables['object'];
  // We should eventually remove the DC object and dc_array code as it only
  // exists to not break legacy implementations.
  try {
    if (isset($object['DC']) && islandora_datastream_access(ISLANDORA_VIEW_OBJECTS, $object['DC'])) {
      $dc = $object['DC']->content;
      $dc_object = DublinCore::importFromXMLString($dc);
    }
  }
  catch (Exception $e) {
    drupal_set_message(t('Error retriveing object %s %t', ['%s' => $object->id, '%t' => $e->getMessage()]), 'error', FALSE);
  }
  $variables['islandora_dublin_core'] = isset($dc_object) ? $dc_object : NULL;
  $variables['dc_array'] = isset($dc_object) ? $dc_object->asArray() : [];
  $variables['islandora_object_label'] = $object->label;
  $variables['theme_hook_suggestions'][] = 'islandora_video__' . str_replace(':', '_', $object->id);
  $variables['parent_collections'] = islandora_get_parents_from_rels_ext($object);
  $variables['metadata'] = islandora_retrieve_metadata_markup($object);
  $variables['description'] = islandora_retrieve_description_markup($object);

  $viewer_dsid = 'MP4';

  if (!$object['MP4']) {
    if (\Drupal::config('islandora_video.settings')->get('islandora_video_play_obj')) {
      if ($object['OBJ'] && $object['OBJ']->mimetype == 'video/mp4') {
        $max_file_size = ((int) \Drupal::config('islandora_video.settings')->get('islandora_video_max_obj_size')) * 1024 * 1024;
        if ($object['OBJ']->size < $max_file_size) {
          $viewer_dsid = 'OBJ';
        }
      }
    }
  }

  $mime = 'video/mp4';

  $video_params = [
    'pid' => $object->id,
  ];
  // Video player.
  if (isset($object[$viewer_dsid]) && islandora_datastream_access(ISLANDORA_VIEW_OBJECTS, $object[$viewer_dsid])) {
    $video_url = Url::fromRoute('islandora.view_datastream_view', ['object' => $object->id, 'datastream' => $viewer_dsid]);
    $video_params += [
      'mime' => $mime,
      'url' => $video_url->toString(),
    ];
  }
  // Thumbnail.
  if (isset($object['TN']) && islandora_datastream_access(ISLANDORA_VIEW_OBJECTS, $object['TN'])) {
    $video_params += [
      'tn' => Url::fromRoute('islandora.view_datastream_view', ['object' => $object->id, 'datastream' => 'TN'], ['absolute' => TRUE]),
    ];

  }

  $viewer = islandora_get_viewer($video_params, 'islandora_video_viewers', $object);
  $variables['islandora_content'] = '';
  if ($viewer) {
    $variables['islandora_content'] = $viewer;
  }
  else {
    $variables['islandora_content'] = NULL;
  }
}
