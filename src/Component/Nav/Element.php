<?php
namespace Sy\Bootstrap\Component\Nav;

class Element extends \Sy\Component\Html\Navigation {

	/**
	 * @param string $label
	 * @param array $data
	 * @return boolean true if the link is active
	 */
	public function addLink($label, array $data) {
		$icon = '';
		$link = null;
		$active = false;
		$attributes = [];
		if (isset($data['fa'])) $icon = '<span class="fas fa-' . $data['fa'] . '"></span> ';
		if (isset($data['page'])) {
			if (isset($data['param'])) {
				$link = \Sy\Bootstrap\Lib\Url::build('page', $data['page'], $data['param']);
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
				$link = \Sy\Bootstrap\Lib\Url::build('page', $data['page']);
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
		$item = $this->addItem($icon . $this->_($label), $link, $attributes);
		if ($active) {
			$item->setAttribute('class', 'active');
		}
		$item->addClass('nav-item');
		return $active;
	}

}
