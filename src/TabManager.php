<?php

namespace Drupal\adminic_toolbar;

class TabManager {

  /**
   * @var \Drupal\adminic_toolbar\DiscoveryManager
   */
  private $discoveryManager;

  /**
   * @var \Drupal\adminic_toolbar\RouteManager
   */
  private $routeManager;

  /**
   * @var array
   */
  private $tabs = [];

  /**
   * @var array
   */
  private $activeTabs = [];

  /**
   * TabManager constructor.
   *
   * @param \Drupal\adminic_toolbar\DiscoveryManager $discoveryManager
   * @param \Drupal\adminic_toolbar\RouteManager $routeManager
   */
  public function __construct(
    DiscoveryManager $discoveryManager,
    RouteManager $routeManager) {
    $this->discoveryManager = $discoveryManager;
    $this->routeManager = $routeManager;
  }

  /**
   * Get all defined tabs from all config files.
   */
  protected function parseTabs() {
    $config = $this->discoveryManager->getConfig();

    $configTabs = [];
    foreach ($config as $configFile) {
      if ($configFile['set']['id'] == 'default' && isset($configFile['set']['tabs'])) {
        foreach ($configFile['set']['tabs'] as $tab) {
          $tab['weight'] = isset($tab['weight']) ? $tab['weight'] : 0;
          $configTabs[] = $tab;
        }
      }
    }
    uasort($configTabs, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    foreach ($configTabs as $tab) {
      $id = $tab['id'];
      $section = isset($tab['section']) ? $tab['section'] : '';
      $route = $tab['route'];
      $isValid = $this->routeManager->isRouteValid($route);
      if ($isValid) {
        $title = isset($tab['title']) ? $tab['title'] : $this->routeManager->getDefaultTitle($route);
        $disabled = isset($tab['disabled']) ? $tab['disabled'] : FALSE;
        $active = FALSE;
        $this->addTab(new Tab($id, $section, $route, $title, $active, $disabled));
      }
    }
  }

  /**
   * Get tab unique key from id.
   *
   * @param \Drupal\adminic_toolbar\Tab $tab
   *   Tab.
   *
   * @return string
   *   Return formated key.
   */
  public function getTabKey(Tab $tab) {
    return $tab->getId();
  }

  /**
   * Add tab.
   *
   * @param \Drupal\adminic_toolbar\Tab $tab
   */
  public function addTab(Tab $tab) {
    $key = $this->getTabKey($tab);
    $this->tabs[$key] = $tab;
    // Remove tab if exists and is disabled
    if (isset($this->tabs[$key]) && $tab->isDisabled()) {
      unset($this->tabs[$key]);
    }

  }

  /**
   * Add tab to active tabs.
   *
   * @param \Drupal\adminic_toolbar\Tab $tab
   */
  public function addActiveTab(Tab $tab) {
    $key = $this->getTabKey($tab);
    $this->activeTabs[$key] = $tab;
  }

  /**
   * Set tab as active.
   *
   * @param string $key
   */
  public function setActive(string $key) {
    $this->tabs[$key]->setActive();
  }

  /**
   * Get tabs.
   *
   * @return array
   */
  public function getTabs() {
    if (empty($this->tabs)) {
      $this->parseTabs();
    }

    return $this->tabs;
  }

  /**
   * Get first active tab.
   *
   * @return \Drupal\adminic_toolbar\Tab
   *   Return first active tab.
   */
  public function getActiveTab() {
    $activeTabs = $this->activeTabs;
    if ($activeTabs) {
      return reset($activeTabs);
    }

    return NULL;
  }

}