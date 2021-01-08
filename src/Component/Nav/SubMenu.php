<?php
namespace Sy\Bootstrap\Component\Nav;

class SubMenu extends \Sy\Component\Html\Element {

	public function __construct() {
		parent::__construct('ul');
		$this->setAttribute('class', 'dropdown-menu');
		$this->addTranslator(LANG_DIR);
	}

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
		if (isset($data['class'])) {
			$attributes['class'] = $data['class'];
		}
		$li = new \Sy\Component\Html\Element('li');
		$item = new \Sy\Component\Html\Element('a');
		$item->addText($icon . $this->_($label));
		$item->setAttributes($attributes);
		$item->setAttribute('href', $link);
		if ($active) {
			$item->setAttribute('class', 'active');
		}
		$item->addClass('dropdown-item');
		$li->addElement($item);
		$this->addElement($li);
		return $active;
	}

}