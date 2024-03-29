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
 * Class for change image position and paragraph visibility  for mobile.
 *
 * @ParagraphsBehavior(
 *   id = "dlog_paragraphs_image_and_text",
 *   label = @Translation("Image and text settings"),
 *   description= @Translation("Settings for image and text paragraph type."),
 *   weight = 0,
 * )
 */
class ImageAndTextBehavior extends ParagraphsBehaviorBase {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(ParagraphsType $paragraphs_type) {
    return $paragraphs_type->id() == 'image_and_text';
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode) {
    $image_position = $paragraph->getBehaviorSetting($this->getPluginId(), 'image_position', 'left');
    $build['#attributes']['class'][] = Html::getClass('paragraph-' . 'image-position-' . $image_position);

    $hide_for_mobile = $paragraph->getBehaviorSetting($this->getPluginId(), 'hide_for_mobile', FALSE);
    $is_hidden = $hide_for_mobile ? 'hidden' : 'visible';
    $build['#attributes']['class'][] = Html::getClass('paragraph-' . 'mobile-' . $is_hidden);
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $form['image_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Image position'),
      '#options' => [
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
      ],
      '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(),
        'image_position', 'left'),
    ];

    $form['hide_for_mobile'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide for the mobile'),
      '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'hide_for_mobile', FALSE),
    ];

    return $form;
  }

}
