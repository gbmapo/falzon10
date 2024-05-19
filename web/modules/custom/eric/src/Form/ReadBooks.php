<?php

namespace Drupal\eric\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\BeforeCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Class ReadBooks.
 */
class ReadBooks extends FormBase {

  protected $step = 1;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'read_books';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if ($this->step == 1) {

      $form['#prefix'] = '<div id="form_step1">';
      $form['#suffix'] = '</div>';


      if (Drupal::currentUser()->hasPermission('add books')) {
        $form['new'] = [
          '#type' => 'submit',
          '#value' => $this->t('New'),
          '#weight' => '0',
        ];
      }

      $currentYear = (int) date('Y');
      $options = [];
      for ($i = 2002; $i < $currentYear + 1; $i++) {
        $options[$i] = $i;
      }
      $options[9999] = $this->t('@number and before', ['@number' => $currentYear - 1]);
      $year = $form_state->getValue('year');
      if ($year == NULL) {
        $year = $currentYear;
      }
      $form['year'] = [
        '#type' => 'select',
        '#title' => $this->t('The books I\'ve read in'),
        '#options' => $options,
        '#default_value' => $year,
        '#ajax' => [
          'callback' => '::yearCallback',
          'wrapper' => 'books',
        ],
      ];

      $form['stringtosearch'] = [
        '#prefix' => '<div id="searcharea">',
        '#type' => 'textfield',
        '#size' => 32,
        '#default_value' => '',
        '#placeholder' => t('Enter at least 3 characters'),
        '#attributes' => ['id' => 'stringtosearch'],
      ];
      $form['submittosearch'] = [
        '#type' => 'submit',
        '#name' => 'search',
        '#value' => t('Search'),
        '#ajax' => [
          'callback' => '::ajaxSearch',
          'wrapper' => 'form_step1',
          'progress' => [
            'type' => 'throbber',
            'message' => NULL,
          ],
        ],
        '#suffix' => '</div>',
        '#states' => [
          'visible' => [
            ':input[name="stringtosearch"]' => ['!value' => ''],
          ],
        ],
      ];


      $form['books'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'books'],
      ];
      $content = '<head><base href="/mybooks/">';
      $content .= file_get_contents('mybooks/index' . $year . '.html');
      $content .= '</head>';
      $form['books']['content'] = [
        '#type' => 'inline_template',
        '#template' => $content,
      ];

    }
    else {

      $form['title'] = [
        '#type' => 'textfield',
        '#title' => t('Title'),
        '#size' => 80,
        '#required' => TRUE,
      ];
      $form['author'] = [
        '#type' => 'textfield',
        '#title' => t('Author'),
        '#size' => 80,
      ];
      $form['titleoriginal'] = [
        '#type' => 'textfield',
        '#title' => t('Original title'),
        '#size' => 80,
      ];
      $form['publication'] = [
        '#type' => 'textfield',
        '#title' => t('Year of publication'),
        '#size' => 30,
      ];
      $form['reading'] = [
        '#type' => 'textfield',
        '#title' => t('Date of reading'),
        '#size' => 30,
      ];
      $form['cover'] = [
        '#type' => 'textfield',
        '#title' => t('Cover'),
        '#size' => 30,
        '#attributes' => [
          'placeholder' => t('xxx|xxx{99|xx{0199'),
        ],
      ];
      $form['backcover'] = [
        '#type' => 'textarea',
        '#title' => t('Back cover'),
        '#cols' => 80,
        '#rows' => 15,
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
        '#url' => Url::fromRoute('eric.read_books'),
      ];

    }

    $form['#attached']['library'][] = 'eric/mybooks';

    return $form;
  }

  public function yearCallback($form, FormStateInterface $form_state) {
    return $form['books'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if ($this->step == 1) {
      if ($form_state->getTriggeringElement()['#name'] == 'search') {
        $values = $form_state->getValues();
        if (mb_strlen($values['stringtosearch']) < 3) {
          $form_state->setErrorByName('stringtosearch', $this->t('You must enter at least 3 characters.'),);
        }
        else {
          $command = 'grep -hir "' . $values['stringtosearch'] . '" mybooks/index20*';
          $output = shell_exec($command);
          if ($output) {
            $form_state->set('stringtosearch', $values['stringtosearch']);
            $form_state->set('output', $output);
          }
          else {
            $form_state->setErrorByName('stringtosearch', $this->t('« %stringtosearch » was not found.', [
              '%stringtosearch' => $values['stringtosearch'],
            ]),);
          }
        }
      }
    }
    parent::validateForm($form, $form_state);
  }

  public function ajaxSearch(array &$form, FormStateInterface $form_state) {

    $response = new AjaxResponse();
    $attachments['library'][] = 'core/drupal.dialog.ajax';
    $response->setAttachments($attachments);

    if ($form_state->hasAnyErrors()) {
      $messages = Drupal::messenger()->deleteAll();
      $response->addCommand(new ReplaceCommand('.message.message-error', ''));
      $messages = [
        '#theme' => 'status_messages',
        '#message_list' => $messages,
      ];
      $response->addCommand(new BeforeCommand('#searcharea', $messages));
    }
    else {
      $form['stringtosearch']['#value'] = '';
      $response->addCommand(new ReplaceCommand(NULL, $form));
      $content = '<head><base href="/mybooks/">';
      $content .= $form_state->getStorage()['output'];
      $content .= '</head>';
      $title = t('Search for « %stringtosearch »', [
        '%stringtosearch' => $form_state->getStorage()['stringtosearch'],
      ]);
      $dialog_options = [
        'width' => '90%',
      ];
      $settings = [];
      $response->addCommand(new OpenModalDialogCommand($title, $content, $dialog_options, $settings));
    }
    return $response;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if ($this->step == 1) {
      if ($form_state->getTriggeringElement()['#name'] == 'search') {
      }
      else {
        $form_state->setRebuild();
        $this->step = 2;
      }
    }
    else {

      foreach ($form_state->getValues() as $key => $value) {

        switch ($key) {
          case 'author':
            $author = $value;
            break;
          case 'backcover':
            $backcover = $value;
            $backcover = str_replace("{", "<i>", $value);
            $backcover = str_replace("}", "</i>", $backcover);
            $backcover = str_replace([
              "\r\n",
              "\n",
              "\r",
            ], '<br />', $backcover);
            break;
          case 'cover':
            if ($value == "") {
              $cover = '';
            }
            else {
              $sNumber = strstr($value, "{");
              $sName = strstr($value, "{", TRUE);
              if ($sNumber == FALSE) {
                $cover = '<A>C<IMG ALT="" SRC="images/' . $value . '.jpg"></A>';
              }
              else {
                $iMin = (int) substr($sNumber, 1, 2);
                $iMax = (int) substr($sNumber, 3, 2);
                $iMax = ($iMax == 0) ? $iMin : $iMax;
                $cover = '';
                for ($i = $iMin; $i <= $iMax; $i++) {
                  $sI = sprintf("%02d", $i);
                  $cover .= '<A>C' . $sI . ' <IMG ALT="" SRC="images/' . $sName . $sI . '.jpg"></A>';
                }
              }
            }
            break;
          case 'publication':
            $publication = $value;
            break;
          case 'reading':
            $reading = $value;
            break;
          case 'title':
            $title = $value;
            break;
          case 'titleoriginal':
            $titleoriginal = $value ? $value . '<br>' : '';
            break;
          default:
        }
      }
      $html = '<div><h1>' . $title . '</h1><p class="justify"><span class="floatleft">' . $author . '<br>' . $titleoriginal . $publication . '<br>' . $reading . '</span>' . $cover . $backcover . '</p></div>';

      $currentYear = (int) date('Y');
      $file = 'mybooks/index' . $currentYear . '.html';

      $content = file_get_contents($file);
      $content = $html . PHP_EOL . $content;
      file_put_contents($file, $content);

      $form_state->setRedirect('eric.read_books');
    }
  }

}
