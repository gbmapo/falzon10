<?php

namespace Drupal\eric\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal;
use Drupal\Core\Url;

/**
 * Class SeenMovies.
 */
class SeenMovies extends FormBase
{

  protected $step = 1;

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'seen_movies';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    if ($this->step == 1) {

      if (Drupal::currentUser()->hasPermission('add movies')) {
        $form['ulysse'] = [
          '#type' => 'submit',
          '#value' => $this->t('Ulysse'),
          '#weight' => '0',
        ];
        $form['tttt'] = [
          '#type' => 'submit',
          '#value' => $this->t('TTTT'),
          '#weight' => '0',
        ];
      }

      $currentYear = (int)date('Y');
      $options = [];
      for ($i = 2004; $i < $currentYear + 1; $i++) {
        $options[$i] = $i;
      }
      $options[9999] = $this->t('@number and before', ['@number' => $currentYear - 1]);
      $year = $form_state->getValue('year');
      if ($year == NULL) {
        $year = $currentYear;
      }
      $form['year'] = [
        '#type' => 'select',
        '#title' => $this->t('The movies I\'ve seen in'),
        '#options' => $options,
        '#default_value' => $year,
        '#ajax' => [
          'callback' => '::yearCallback',
          'wrapper' => 'movies',
        ],
      ];

      $form['movies'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'movies'],
      ];
      $content = '<head><base href="/mymovies/">';
      $content .= file_get_contents('mymovies/index' . $year . '.html');
      $content .= '</head>';
      $form['movies']['content'] = [
        '#type' => 'inline_template',
        '#template' => $content,
      ];

    }
    else {

      switch ($this->step) {
        case '2U':
          $options = [
            0 => '<img alt="Helas" src="/mymovies/0Helas.gif">',
            1 => '<img alt="Bof" src="/mymovies/1Bof.gif">',
            2 => '<img alt="Passable" src="/mymovies/2Passable.gif">',
            3 => '<img alt="Bien" src="/mymovies/3Bien.gif">',
            4 => '<img alt="Bravo" src="/mymovies/4Bravo.gif">',
          ];
          $form['ulysse'] = [
            '#type' => 'radios',
            '#options' => $options,
            '#default_value' => 4,
            '#weight' => 1,
          ];
          $form['comment'] = [
            '#type' => 'textarea',
            '#title' => t('Comment'),
            '#cols' => 80,
            '#rows' => 2,
            '#weight' => 4,
          ];
          $form['titleoriginal'] = [
            '#type' => 'textfield',
            '#title' => t('Original title'),
            '#size' => 80,
            '#weight' => 7,
          ];
          $form['country'] = [
            '#type' => 'textfield',
            '#title' => t('Country'),
            '#size' => 30,
            '#weight' => 8,
          ];
          $form['duration'] = [
            '#type' => 'textfield',
            '#title' => t('Duration'),
            '#size' => 30,
            '#weight' => 9,
          ];
          $form['director'] = [
            '#type' => 'textfield',
            '#title' => t('Director'),
            '#size' => 30,
            '#weight' => 10,
          ];
          $form['actors'] = [
            '#type' => 'textarea',
            '#title' => t('Actors'),
            '#cols' => 80,
            '#rows' => 2,
            '#weight' => 11,
          ];
          $form['movietheater'] = [
            '#type' => 'textfield',
            '#title' => t('Movie theater'),
            '#size' => 30,
            '#weight' => 12,
          ];
          break;

        case '2T':
          $options = [
            0 => '<img alt="0T" src="/mymovies/label_0T.gif">',
            1 => '<img alt="T" src="/mymovies/label_1T.gif">',
            2 => '<img alt="TT" src="/mymovies/label_2T.gif">',
            3 => '<img alt="TTT" src="/mymovies/label_3T.gif">',
            4 => '<img alt="TTTT" src="/mymovies/label_4T.gif">',
          ];
          $form['tttt'] = [
            '#type' => 'radios',
            '#options' => $options,
            '#default_value' => 0,
            '#weight' => 1,
          ];
          $form['tvshow'] = [
            '#type' => 'checkbox',
            '#title' => t('TV Show?'),
            '#weight' => 4,
          ];
          break;

        default:
          break;
      }

      $form['date'] = [
        '#type' => 'date',
        '#title' => $this->t('Date'),
        '#default_value' => [date('Y-m-d'),],
        '#weight' => 2,
      ];

      $form['title'] = [
        '#type' => 'textfield',
        '#title' => t('Title'),
        '#size' => 80,
        '#required' => TRUE,
        '#weight' => 3,
      ];

      $form['review'] = [
        '#type' => 'textarea',
        '#title' => t('Text of the review'),
        '#cols' => 80,
        '#rows' => 15,
        '#weight' => 5,
      ];

      $form['signature'] = [
        '#type' => 'textfield',
        '#title' => t('Signature'),
        '#size' => 30,
        '#weight' => 6,
      ];

      $form['trailer'] = [
        '#type' => 'textarea',
        '#title' => t('Trailer'),
        '#placeholder' => '<iframe blabla ></iframe>',
        '#cols' => 80,
        '#rows' => 3,
                '#weight' => 7,
      ];

      $form['actions'] = [
        '#type' => 'actions',
      ];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => t('Save'),
        '#weight' => 99,
      ];
      $form['actions']['previous'] = [
        '#type' => 'link',
        '#title' => $this->t('Cancel'),
        '#attributes' => [
          'class' => ['form-submit'],
        ],
        '#weight' => 99,
        '#url' => Url::fromRoute('eric.seen_movies'),
      ];


    }

    $form['#attached']['library'][] = 'eric/mymovies';

    return $form;
  }

  public function yearCallback($form, FormStateInterface $form_state)
  {
    return $form['movies'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
//     foreach ($form_state->getValues() as $key => $value) {
//     }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    if ($this->step == 1) {
      $values = $form_state->getValues();
      switch ($values['op']) {
        case 'Ulysse':
          $form_state->setRebuild();
          $this->step = '2U';
          break;
        case 'TTTT':
          $form_state->setRebuild();
          $this->step = '2T';
          break;
        default:
      }
    }
    else {

      $transcod = get_html_translation_table(HTML_ENTITIES);
      $tttt = ["label_0T.gif", "label_1T.gif", "label_2T.gif", "label_3T.gif", "label_4T.gif"];
      $ulysse = ["0Helas.gif", "1Bof.gif", "2Passable.gif", "3Bien.gif", "4Bravo.gif"];

      foreach ($form_state->getValues() as $key => $value) {

        switch ($key) {
          case 'actors':
            $actors = $value ? strtr(' Avec : ' . $value . '.', $transcod) : '';
            break;
          case 'comment':
            $comment = strtr($value, $transcod);
            $comment = str_replace(["\r\n", "\n", "\r"], '<br />', $comment);
            break;
          case 'country':
            $country = strtr($value, $transcod);
            break;
          case 'date':
            $date = substr($value, 8, 2) . "/" . substr($value, 5, 2) . "/" . substr($value, 0, 4);
            break;
          case 'director':
            $director = strtr(' Réalisateur : ' . $value . '.', $transcod);
            break;
          case 'duration':
            $duration = ' (' . $value . ').';
            break;
          case 'movietheater':
            $movietheater = $value ? strtr('Cinéma : ' . $value, $transcod) : '';
            break;
          case 'review':
            $review = strtr($value, $transcod);
            $review = str_replace("{", "<i>", $value);
            $review = str_replace("}", "</i>", $review);
            $review = str_replace(["\r\n", "\n", "\r"], '<br />', $review);
            break;
          case 'signature':
            $signature = strtr($value, $transcod);
            break;
          case 'title':
            $title = strtr($value, $transcod);
            break;
          case 'titleoriginal':
            $titleoriginal = strtr('[' . $value . '] ', $transcod);
            break;
          case 'tttt':
            $image = $tttt[$value];
            break;
          case 'trailer':
            if ($value != '') {
              $value = str_replace('"', '', $value);
              $aTemp = explode(" ", $value);
              foreach ($aTemp as $item) {
                switch (TRUE) {
                  case (substr($item, 0, 5)=='width'):
                    $width = substr($item, 6);
                    break;
                  case (substr($item, 0, 6)=='height'):
                    $height = substr($item, 7);
                    break;
                  case (substr($item, 0, 3)=='src'):
                    $src = substr($item, 4);
                    break;
                  default:
                }
              }
              $width = round($width * 300 / $height);
              $height = 300;
              $trailer = ' <a href="javascript:void jQuery.colorbox({html:\'';
              $trailer .= '<iframe width=' . $width . ' height=' . $height . ' src=' . $src . ' allowfullscreen></iframe>';
              $trailer .= '\'})"><IMG SRC="external-link.svg"></a>';
            }
            else {
              $trailer = '';
            }
            break;
          case 'tvshow':
            $tvshow = $value;
            break;
          case 'ulysse':
            $image = $ulysse[$value];
            break;
          default:
        }
      }
      switch ($this->step) {
        case '2T':
          $class = ($tvshow == 1) ? "TVShow" : "";
          $html = '<div><h1 class="tttt' . $class . '"><img src="' . $image . '">' . $title . $trailer . '<span class="date">' . $date . '</span></h1><p class="justify">' . $review . '</p>' . ($signature == '' ? '' : '<p class="signature">' . $signature . '</p>') . '<hr></div>';
          break;
        case '2U':
          $html = '<div><h1 class="ulysse">' . $title . $trailer . '<span class="date">' . $date . '</span></h1><h2 class="justify">' . $comment . '</h2><p class="justify"><img class="imageulysse" src="' . $image . '">' . $review . '</p><p class="signature">' . $signature . '</p><h5>' . ($titleoriginal == '[] ' ? '' : $titleoriginal) . $country . $duration . $director . $actors . '</h5><p>' . $movietheater . '</p><hr></div>';
          break;
        default:
      }

      $currentYear = (int)date('Y');
      $file = 'mymovies/index' . $currentYear . '.html';

      $content = file_get_contents($file);
      $content = $html . PHP_EOL . $content;
      file_put_contents($file, $content);

      $form_state->setRedirect('eric.seen_movies');
    }
  }

}
