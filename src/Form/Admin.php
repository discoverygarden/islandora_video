<?php

namespace Drupal\islandora_video\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

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

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['islandora_video.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    module_load_include('inc', 'islandora', 'includes/solution_packs');
    $form = [];
    // Get viewer table.
    $viewer_table = islandora_viewers_form('islandora_video_viewers', 'video/mp4');
    $form += $viewer_table;

    // Viewer stuff.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['reset'] = [
      '#type' => 'submit',
      '#value' => t('Reset to defaults'),
      '#weight' => 1,
      '#submit' => [
        'islandora_video_admin_submit'
        ],
    ];

    // Playback options.
    $form['playback'] = [
      '#type' => 'fieldset',
      '#title' => t('Playback'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $description = t("When Off (default), only the MP4 datastream will be played in a viewer (if a viewer is configured above). With this box checked, the uploaded file (OBJ datastream) will be played if:");
    $description .= '<ul><li>' . t('the MP4 datastream is not present') . '</li>';
    $description .= '<li>' . t('the OBJ datastream has mimetype video/mp4') . '</li>';
    $description .= '<li>' . t('the OBJ datastream is smaller than the size configured below:') . '</li></ul>';
    $form['playback']['islandora_video_play_obj'] = [
      '#type' => 'checkbox',
      '#title' => t('Play OBJ datastream in viewer'),
      '#description' => $description,
      '#default_value' => \Drupal::config('islandora_video.settings')->get('islandora_video_play_obj'),
    ];
    $form['playback']['islandora_video_max_obj_size'] = [
      '#type' => 'textfield',
      '#title' => t('Maximum file size for playing OBJ (in MB)'),
      '#description' => t("Set a maximum size (in megabytes) for sending the OBJ datastream to the player."),
      '#default_value' => \Drupal::config('islandora_video.settings')->get('islandora_video_max_obj_size'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_video_play_obj"]' => [
            'checked' => TRUE
            ]
          ]
        ],
    ];
    // Derivative Options.
    $form['derivatives'] = [
      '#type' => 'fieldset',
      '#title' => t('Derivatives'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    // FFMPEG configuration.
    $form['derivatives']['islandora_video_ffmpeg_path'] = [
      '#type' => 'textfield',
      '#title' => t('Path to FFmpeg'),
      '#default_value' => \Drupal::config('islandora_video.settings')->get('islandora_video_ffmpeg_path'),
      '#description' => t('Path to the FFmpeg binary. For example: <kbd>/usr/local/bin/ffmpeg</kbd>  . Used if creating TN, MP4, or MKV derivatives.'),
      '#required' => TRUE,
    ];
    // TN derivatives.
    $form['derivatives']['islandora_video_make_thumbnail_locally'] = [
      '#type' => 'checkbox',
      '#title' => t('Create TN datastream locally'),
      '#description' => t('If On (default), the Drupal server will use FFmpeg to create a thumbnail screenshot from the video, or fall back to a default thumbnail. If Off, no thumbnail will be created.'),
      '#default_value' => \Drupal::config('islandora_video.settings')->get('islandora_video_make_thumbnail_locally'),
    ];
    // MP4 derivatives.
    $form['derivatives']['islandora_video_make_mp4_locally'] = [
      '#type' => 'checkbox',
      '#title' => t('Create MP4 datastream locally'),
      '#description' => t('If On (default), the Drupal server will use FFmpeg to create a web-friendly mp4 version of the uploaded file. Disable if another server creates derivatives, or if you intend to play the uploaded files directly (see Playback options above). This feature requires a valid MP4 audio codec.'),
      '#default_value' => \Drupal::config('islandora_video.settings')->get('islandora_video_make_mp4_locally'),
    ];
    $form['derivatives']['islandora_video_mp4_audio_codec'] = [
      '#type' => 'textfield',
      '#title' => t('MP4 audio codec'),
      '#description' => t("Defaults to libfaac, a non-free encoder. FFmpeg must have been compiled from source with that encoder enabled. See @FFMPEG for more info.", [
        '@FFMPEG' => \Drupal::l(t("FFmpeg's AAC encoding guide"), \Drupal\Core\Url::fromUri('https://trac.ffmpeg.org/wiki/Encode/AAC'))
        ]),
      '#default_value' => \Drupal::config('islandora_video.settings')->get('islandora_video_mp4_audio_codec'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_video_make_mp4_locally"]' => [
            'checked' => TRUE
            ]
          ]
        ],
    ];
    // MKV derivatives.
    $form['derivatives']['islandora_video_make_archive'] = [
      '#type' => 'checkbox',
      '#title' => t('Create MKV datastream locally'),
      '#description' => t('If On (default), the Drupal server will use FFmpeg to create a Matroska container derivative. This may be useful for archival purposes, but is not used by Islandora.'),
      '#default_value' => \Drupal::config('islandora_video.settings')->get('islandora_video_make_archive'),
    ];
    // OGG/Theora derivatives.
    $form['derivatives']['islandora_video_make_ogg_locally'] = [
      '#type' => 'checkbox',
      '#title' => t('Create OGG datastream locally'),
      '#description' => t("If On (default is Off), the Drupal server will use ffmpeg2theora to create an Ogg Theora container derivative. This is a legacy option only, as this datastream is no longer used by Islandora."),
      '#default_value' => \Drupal::config('islandora_video.settings')->get('islandora_video_make_ogg_locally'),
    ];
    $form['derivatives']['islandora_video_ffmpeg2theora_path'] = [
      '#type' => 'textfield',
      '#title' => t('Path to ffmpeg2theora executable'),
      '#description' => t('Path to Theora conversion program on your server'),
      '#default_value' => \Drupal::config('islandora_video.settings')->get('islandora_video_ffmpeg2theora_path'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_video_make_ogg_locally"]' => [
            'checked' => TRUE
            ]
          ]
        ],
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Don't validate if resetting.
    $op = $form_state->get([
      'clicked_button',
      '#id',
    ]);
    if ($op == 'edit-reset') {
      return;
    }
    // Ensure the mp4 audio codec is present if MP4 derivative is enabled.
    if ($form_state->getValue([
      'islandora_video_make_mp4_locally'
      ]) == TRUE) {
      // If no value is given, set the form to use and validate the default.
      $raw_value = $form_state->getValue([
        'islandora_video_mp4_audio_codec'
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
        'islandora_video_ffmpeg_path'
        ]) !== \Drupal::config('islandora_video.settings')->get('islandora_video_ffmpeg_path') ? $form_state->getValue([
        'islandora_video_ffmpeg_path'
        ]) : \Drupal::config('islandora_video.settings')->get('islandora_video_ffmpeg_path'));
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

  public function _submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $op = $form_state->get(['clicked_button', '#id']);
    switch ($op) {
      case 'edit-reset':
        \Drupal::config('islandora_video.settings')->clear('islandora_video_viewers')->save();
        \Drupal::config('islandora_video.settings')->clear('islandora_video_ffmpeg_path')->save();
        \Drupal::config('islandora_video.settings')->clear('islandora_video_make_archive')->save();
        \Drupal::config('islandora_video.settings')->clear('islandora_video_make_mp4_locally')->save();
        \Drupal::config('islandora_video.settings')->clear('islandora_video_make_ogg_locally')->save();
        \Drupal::config('islandora_video.settings')->clear('islandora_video_ffmpeg2theora_path')->save();
        \Drupal::config('islandora_video.settings')->clear('islandora_video_mp4_audio_codec')->save();
        \Drupal::config('islandora_video.settings')->clear('islandora_video_play_obj')->save();
        \Drupal::config('islandora_video.settings')->clear('islandora_video_max_obj_size')->save();
        break;
    }
  }

}
