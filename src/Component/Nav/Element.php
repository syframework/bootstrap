<?php
namespace Sy\Bootstrap\Component\Nav;

use Sy\Component\Html\Navigation;
use Sy\Component\Html\Link;
use Sy\Bootstrap\Lib\Url;

class Element extends Navigation {

	/**
	 * @param  string $label
	 * @param  array $data
	 * @return boolean true if the link is active
	 */
	public function addLink($label, array $data) {
		$icon = '';
		$link = null;
		$active = false;
		$attributes = [];
		if (isset($data['icon'])) $icon = $data['icon'];
		if (isset($data['page'])) {
			if (isset($data['param'])) {
				$link = Url::build('page', $data['page'], $data['param']);
				$active = ($this->get(CONTROLLER_TRIGGER) === 'page' and $this->get(ACTION_TRIGGER) === $data['page']);
				if ($active) {
					foreach ($data['param'] as $k => $v) {
						if ((string)$this->get($k) !== (string)$v) {
							$active = false;
							break;
						}
					}
				}
			} else {
				$link = Url::build('page', $data['page']);
				$active = ($this->get(CONTROLLER_TRIGGER) === 'page' and $this->get(ACTION_TRIGGER) === $data['page']);
			}
		} elseif (isset($data['url'])) {
			$link = $data['url'];
		}
		if (isset($data['target'])) {
			$attributes['target'] = $data['target'];
		}
		$attributes['class'] = 'nav-link';
		if (isset($data['class'])) {
			$attributes['class'] .= ' ' . $data['class'];
		}
		if ($active) {
			$attributes['class'] .= ' active';
		}
		$this->addItem(new Link(\Sy\Component\WebComponent::concat($icon, ' ', $this->_($label)), $link, $attributes), attributes: ['class' => 'nav-item']);
		return $active;
	}

}