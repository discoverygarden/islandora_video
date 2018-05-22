<?php

namespace Drupal\islandora_video\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Module settings form.
 */
class Admin extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_video_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('islandora_video.settings');

    $config->set('islandora_video_make_thumbnail_locally', $form_state->getValue('islandora_video_make_thumbnail_locally'));
    $config->set('islandora_video_ffmpeg_path', $form_state->getValue('islandora_video_ffmpeg_path'));
    $config->set('islandora_video_make_archive', $form_state->getValue('islandora_video_make_archive'));
    $config->set('islandora_video_make_mp4_locally', $form_state->getValue('islandora_video_make_mp4_locally'));
    $config->set('islandora_video_make_ogg_locally', $form_state->getValue('islandora_video_make_ogg_locally'));
    $config->set('islandora_video_ffmpeg2theora_path', $form_state->getValue('islandora_video_ffmpeg2theora_path'));
    $config->set('islandora_video_mp4_audio_codec', $form_state->getValue('islandora_video_mp4_audio_codec'));
    $config->set('islandora_video_play_obj', $form_state->getValue('islandora_video_play_obj'));
    $config->set('islandora_video_max_obj_size', $form_state->getValue('islandora_video_max_obj_size'));

    $config->save();
    islandora_set_viewer_info('islandora_video_viewers', $form_state->getValue('islandora_video_viewers'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['islandora_video.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    module_load_include('inc', 'islandora', 'includes/solution_packs');
    $form = [];
    // Get viewer table.
    $viewer_table = islandora_viewers_form('islandora_video_viewers', 'video/mp4');
    $form += $viewer_table;

    // Playback options.
    $form['playback'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Playback'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $description = $this->t("When Off (default), only the MP4 datastream will be played in a viewer (if a viewer is configured above). With this box checked, the uploaded file (OBJ datastream) will be played if:");
    $description .= '<ul><li>' . $this->t('the MP4 datastream is not present') . '</li>';
    $description .= '<li>' . $this->t('the OBJ datastream has mimetype video/mp4') . '</li>';
    $description .= '<li>' . $this->t('the OBJ datastream is smaller than the size configured below:') . '</li></ul>';
    $form['playback']['islandora_video_play_obj'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Play OBJ datastream in viewer'),
      '#description' => $description,
      '#default_value' => $this->config('islandora_video.settings')->get('islandora_video_play_obj'),
    ];
    $form['playback']['islandora_video_max_obj_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum file size for playing OBJ (in MB)'),
      '#description' => $this->t("Set a maximum size (in megabytes) for sending the OBJ datastream to the player."),
      '#default_value' => $this->config('islandora_video.settings')->get('islandora_video_max_obj_size'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_video_play_obj"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    // Derivative Options.
    $form['derivatives'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Derivatives'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    // FFMPEG configuration.
    $form['derivatives']['islandora_video_ffmpeg_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to FFmpeg'),
      '#default_value' => $this->config('islandora_video.settings')->get('islandora_video_ffmpeg_path'),
      '#description' => $this->t('Path to the FFmpeg binary. For example: <kbd>/usr/local/bin/ffmpeg</kbd>  . Used if creating TN, MP4, or MKV derivatives.'),
      '#required' => TRUE,
    ];
    // TN derivatives.
    $form['derivatives']['islandora_video_make_thumbnail_locally'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create TN datastream locally'),
      '#description' => $this->t('If On (default), the Drupal server will use FFmpeg to create a thumbnail screenshot from the video, or fall back to a default thumbnail. If Off, no thumbnail will be created.'),
      '#default_value' => $this->config('islandora_video.settings')->get('islandora_video_make_thumbnail_locally'),
    ];
    // MP4 derivatives.
    $form['derivatives']['islandora_video_make_mp4_locally'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create MP4 datastream locally'),
      '#description' => $this->t('If On (default), the Drupal server will use FFmpeg to create a web-friendly mp4 version of the uploaded file. Disable if another server creates derivatives, or if you intend to play the uploaded files directly (see Playback options above). This feature requires a valid MP4 audio codec.'),
      '#default_value' => $this->config('islandora_video.settings')->get('islandora_video_make_mp4_locally'),
    ];
    $form['derivatives']['islandora_video_mp4_audio_codec'] = [
      '#type' => 'textfield',
      '#title' => $this->t('MP4 audio codec'),
      '#description' => $this->t("Defaults to libfaac, a non-free encoder. FFmpeg must have been compiled from source with that encoder enabled. See @FFMPEG for more info.", [
        '@FFMPEG' => Link::fromTextAndUrl($this->t("FFmpeg's AAC encoding guide"), Url::fromUri('https://trac.ffmpeg.org/wiki/Encode/AAC'))->toString(),
      ]),
      '#default_value' => $this->config('islandora_video.settings')->get('islandora_video_mp4_audio_codec'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_video_make_mp4_locally"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    // MKV derivatives.
    $form['derivatives']['islandora_video_make_archive'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create MKV datastream locally'),
      '#description' => $this->t('If On (default), the Drupal server will use FFmpeg to create a Matroska container derivative. This may be useful for archival purposes, but is not used by Islandora.'),
      '#default_value' => $this->config('islandora_video.settings')->get('islandora_video_make_archive'),
    ];
    // OGG/Theora derivatives.
    $form['derivatives']['islandora_video_make_ogg_locally'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create OGG datastream locally'),
      '#description' => $this->t("If On (default is Off), the Drupal server will use ffmpeg2theora to create an Ogg Theora container derivative. This is a legacy option only, as this datastream is no longer used by Islandora."),
      '#default_value' => $this->config('islandora_video.settings')->get('islandora_video_make_ogg_locally'),
    ];
    $form['derivatives']['islandora_video_ffmpeg2theora_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to ffmpeg2theora executable'),
      '#description' => $this->t('Path to Theora conversion program on your server'),
      '#default_value' => $this->config('islandora_video.settings')->get('islandora_video_ffmpeg2theora_path'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_video_make_ogg_locally"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Ensure the mp4 audio codec is present if MP4 derivative is enabled.
    if ($form_state->getValue([
      'islandora_video_make_mp4_locally',
    ]) == TRUE) {
      // If no value is given, set the form to use and validate the default.
      $raw_value = $form_state->getValue([
        'islandora_video_mp4_audio_codec',
      ]);
      $codec = strtolower(trim($raw_value));
      if (!$codec) {
        $codec = 'libfaac';
      }
      $form_state->setValue(['islandora_video_mp4_audio_codec'], $codec);
      if (preg_match('/[^0-9a-zA-Z_-]/', $codec) === 1) {
        $form_state->setErrorByName('islandora_video_mp4_audio_codec', "The value entered for MP4 audio codec contains forbidden characters.");
        return;
      }

      // Test that FFMPEG path is valid.
      $ffmpeg = ($form_state->getValue([
        'islandora_video_ffmpeg_path',
      ]) !== $this->config('islandora_video.settings')->get('islandora_video_ffmpeg_path') ? $form_state->getValue([
        'islandora_video_ffmpeg_path',
      ]) : $this->config('islandora_video.settings')->get('islandora_video_ffmpeg_path'));
      if (preg_match('/[^0-9a-zA-Z\\/\\\\_-]/', $ffmpeg) === 1) {
        $form_state->setErrorByName('islandora_video_ffmpeg_path', "The value entered for FFmpeg path contains forbidden characters.");
        return;
      }
      $command = $ffmpeg . ' -version';
      exec($command, $output, $ret);
      if ($ret !== 0) {
        $form_state->setErrorByName('islandora_video_ffmpeg_path', "FFmpeg was not found. A valid FFmpeg path is required if MP4 derivative generation is enabled.");
      }
      else {
        // Test that the specified codec is present.
        unset($output);
        $command = $ffmpeg . ' -encoders 2>/dev/null | grep "^ ...... ' . $codec . ' "';
        exec($command, $output, $ret);
        if (!$output) {
          $form_state->setErrorByName('islandora_video_mp4_audio_codec', 'The selected MP4 codec was not found in ffmpeg. Try using aac or enable the desired codec.');
        }
      }
    }
  }

}
