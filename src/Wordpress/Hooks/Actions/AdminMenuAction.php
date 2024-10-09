<?php

namespace AIMuse\Wordpress\Hooks\Actions;

/**
 * Action Hook: Fires before the administration menu loads in the admin.
 *
 * @link https://developer.wordpress.org/reference/hooks/admin_menu_action/
 */
class AdminMenuAction extends Action
{
  public $subMenuPages = [
    [
      'page_title' => 'Dashboard',
      'menu_title' => 'Dashboard',
      'react_route' => '#/',
      'capability' => 'manage_options',
      'callback' => 'display',
    ],
    [
      'page_title' => 'Image Generator',
      'menu_title' => 'Image Generator',
      'react_route' => '#/image-generator',
      'capability' => 'manage_options',
      'callback' => 'display',
    ],
    [
      'page_title' => 'Content Generator',
      'menu_title' => 'Content Generator',
      'react_route' => '#/content-generator',
      'capability' => 'manage_options',
      'callback' => 'display',
    ],
    [
      'page_title' => 'Prompt Templates',
      'menu_title' => 'Prompt Templates',
      'react_route' => '#/prompt-templates',
      'capability' => 'manage_options',
      'callback' => 'display',
    ],
    [
      'page_title' => 'Playground',
      'menu_title' => 'Playground',
      'react_route' => '#/playground',
      'capability' => 'manage_options',
      'callback' => 'display',
    ],
    [
      'page_title' => 'Fine-tuning',
      'menu_title' => 'Fine-tuning',
      'react_route' => '#/finetuning',
      'capability' => 'manage_options',
      'callback' => 'display',
    ],
    [
      'page_title' => 'Settings',
      'menu_title' => 'Settings',
      'react_route' => '#/settings',
      'capability' => 'manage_options',
      'callback' => 'display',
    ],
    [
      'page_title' => 'Support & Feedback',
      'menu_title' => 'Support & Feedback',
      'react_route' => '#/support',
      'capability' => 'manage_options',
      'callback' => 'display',
    ]
  ];

  public function __construct()
  {
    $this->name = 'admin_menu';
  }

  public function handle(string $context)
  {
    add_menu_page(
      'AI Muse',
      'AI Muse',
      'manage_options',
      aimuse()->name(),
      array($this, 'display'),
      $this->icon(),
      27
    );

    foreach ($this->subMenuPages as $subMenuPage) {
      add_submenu_page(
        aimuse()->name(),
        $subMenuPage['page_title'],
        $subMenuPage['menu_title'],
        $subMenuPage['capability'],
        aimuse()->name() . $subMenuPage['react_route'],
        array($this, $subMenuPage['callback'])
      );
    }

    remove_submenu_page(aimuse()->name(), aimuse()->name());
  }

  public function display()
  {
    echo '<div class="wrap" id="aimuse-admin"></div>';
  }

  public function icon()
  {
    return aimuse()->url('public/assets/images/icon.png');
  }
}
