<?php

namespace Drupal\swap_blocks\Plugin\paragraphs\Behavior;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * @ParagraphsBehavior(
 *   id = "dlog_paragraphs_image_and_text",
 *   label = @Translation("Image and text settings"),
 *   description= @Translation("Settings for image and text paragraph type."),
 *   weight = 0,
 * )
 */
class ImageAndTextBehavior extends ParagraphsBehaviorBase {

  /**
   * @param \Drupal\paragraphs\Entity\ParagraphsType $paragraphs_type
   *
   * @return bool
   */
  public static function isApplicable(ParagraphsType $paragraphs_type) {
    return $paragraphs_type->id() == 'image_and_text';
  }

  /**
   * @param array $build
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   * @param $view_mode
   *
   * @return void
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode) {
    $image_position = $paragraph->getBehaviorSetting($this->getPluginId(), 'image_position', 'left');
    $build['#attributes']['class'][] = Html::getClass('paragraph-' . 'image-position-' . $image_position);
  }

  /**
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $form['image_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Image position'),
      '#options' => [
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
      ],
      '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'image_position', 'left'),
    ];

    return $form;
  }

}
