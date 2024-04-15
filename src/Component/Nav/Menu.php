<?php
namespace Sy\Bootstrap\Component\Nav;

class Menu extends Element {

	private $menu;

	public function __construct(array $menu = []) {
		parent::__construct();
		$this->menu = $menu;

		$this->mount(function () {
			$this->init($this->menu);
		});
	}

	public function addDropdown($title, SubMenu $subMenu, $active = false) {
		$a = new \Sy\Component\Html\Element('a');
		$a->setAttributes([
			'href'           => '#',
			'class'          => 'nav-link dropdown-toggle',
			'data-bs-toggle' => 'dropdown',
		]);
		$a->addText($title);
		$li = new \Sy\Component\Html\Element('li');
		$li->setAttribute('class', 'nav-item dropdown');
		if ($active) {
			$a->addClass('active');
		}
		$li->addElement($a);
		$li->addElement($subMenu);
		return $this->addElement($li);
	}

	public function add(array $menu) {
		foreach ($menu as $label => $array) {
			if (isset($array['menu'])) { // have sub menu
				$subMenu = new SubMenu();
				if (isset($array['class'])) $subMenu->addClass($array['class']);
				$active = false;
				foreach ($array['menu'] as $key => $value) {
					if ($value === '-') {
						$li = new \Sy\Component\Html\Element('div');
						$li->setAttribute('class', 'dropdown-divider');
						$li->addText(' ');
						$subMenu->addElement($li);
					} elseif (is_string($value)) {
						$li = new \Sy\Component\Html\Element('h6');
						$li->setAttribute('class', 'dropdown-header');
						$li->addText($this->_($value));
						$subMenu->addElement($li);
					} else {
						if ($subMenu->addLink($key, $value)) $active = true;
					}
				}
				$icon = '';
				if (isset($array['icon'])) $icon = $array['icon'];
				$this->addDropdown(\Sy\Component\WebComponent::concat($icon, ' ', $this->_($label)), $subMenu, $active);
			} else {
				$this->addLink($label, $array);
			}
		}
	}

	private function init($menu) {
		$this->addTranslator(LANG_DIR);
		$this->addClass('navbar-nav');
		$this->add($menu);
	}

}