<?php
namespace Sy\Bootstrap\Component;

use Sy\Bootstrap\Lib\Url;

abstract class Page extends \Sy\Component\WebComponent {

	private $method;

	public function __construct($method = null) {
		parent::__construct();
		// hack to select the default menu
		if (is_null($this->get(CONTROLLER_TRIGGER))) {
			$_GET[CONTROLLER_TRIGGER] = 'page';
		}
		$this->method = $method;
		$this->addTranslator(LANG_DIR);
		$this->actionDispatch(ACTION_TRIGGER, 'index');
	}

	public function indexAction() {
		$method = is_null($this->method) ? str_replace('-', '_', $this->get(ACTION_TRIGGER, 'home')) : (string) $this->method;
		// hack to select the default menu
		if ($method === 'home') $_GET[ACTION_TRIGGER] = 'home';
		if (in_array($method, ['__construct', '__call', 'indexAction', '_menu'])) return;

		// Default meta og
		\Sy\Bootstrap\Lib\HeadData::addMeta('og:type', 'website');
		\Sy\Bootstrap\Lib\HeadData::addMeta('og:image', PROJECT_URL . WEB_ROOT . '/assets/img/logo.png');
		\Sy\Bootstrap\Lib\HeadData::addMeta('og:site_name', PROJECT);

		$this->$method();
	}

	public function __call($name, $arguments) {
		$name = str_replace('_', '-', $name);
		$this->setTemplateFile(__DIR__ . '/Page/Page.html');

		// Rel canonical
		$get = $_GET;
		unset($get[CONTROLLER_TRIGGER]);
		unset($get[ACTION_TRIGGER]);
		unset($get['sy_language']);
		$url = PROJECT_URL . Url::build('page', $name, $get);
		\Sy\Bootstrap\Lib\HeadData::setCanonical($url);

		// Detect language
		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();

		// Retrieve page
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		$page = $service->page->retrieve(['id' => $name, 'lang' => $lang]);

		// Create
		if ($service->user->getCurrentUser()->hasPermission('page-create')) {
			$form = new \Sy\Bootstrap\Component\Page\Create();
			$form->getField('lang')->setAttribute('value', $lang);
			$this->setComponent('NEW_PAGE_FORM', $form);
			$this->addJsCode("$('#new-page-modal').has('div.alert').modal('show');");
			$this->setBlock('CREATE_BTN_BLOCK');
			$this->setBlock('CREATE_MODAL_BLOCK');
		}

		// No page found
		if (empty($page) and $name === '404') return;
		if (empty($page)) throw new \Sy\Bootstrap\Application\PageNotFoundException();

		// Show page content
		\Sy\Bootstrap\Lib\HeadData::setTitle($page['title']);
		\Sy\Bootstrap\Lib\HeadData::setDescription($page['description']);

		// Layout file
		$l = file_exists(TPL_DIR . "/Application/Page/layout/$name.html") ? $name : '_default';
		$layout = new \Sy\Component\WebComponent();
		$layout->setTemplateFile(TPL_DIR . "/Application/Page/layout/$l.html");
		$layout->addTranslator(LANG_DIR);
		if (isset($arguments['LAYOUT'])) {
			foreach ($arguments['LAYOUT'] as $k => $v) {
				if ($v instanceof \Sy\Component) {
					$layout->setComponent($k, $v);
				} else {
					$layout->setVar($k, $v);
				}
			}
		}

		// Content file
		$f = TPL_DIR . "/Application/Page/content/$lang/$name.html";
		if (!file_exists($f)) {
			$f = TPL_DIR . "/Application/Page/content/$name.html";
		}
		if (!file_exists($f)) {
			touch($f);
		}
		$content = new \Sy\Component\WebComponent();
		$content->setTemplateFile($f);
		$content->addTranslator(LANG_DIR);
		if (isset($arguments['CONTENT'])) {
			foreach ($arguments['CONTENT'] as $k => $v) {
				if ($v instanceof \Sy\Component) {
					$content->setComponent($k, $v);
				} else {
					$content->setVar($k, $v);
				}
			}
		}
		$layout->setVars([
			'_PROJECT'  => PROJECT,
			'_WEB_ROOT' => WEB_ROOT,
		]);
		$menu = array_key_exists('MENU', $arguments) ? $arguments['MENU'] : $this->_menu();
		if (!empty($menu)) {
			$layout->setComponent('_NAV', $menu);
		}
		$layout->setComponent('_CONTENT', $content);
		$this->setComponent('LAYOUT', $layout);
		$this->setBlock('LAYOUT_BLOCK');

		// Page CSS and JS
		if (file_exists(TPL_DIR . "/Application/Page/css/$name.css")) {
			$this->addCssCode(file_get_contents(TPL_DIR . "/Application/Page/css/$name.css"));
		}
		if (file_exists(TPL_DIR . "/Application/Page/js/$name.js")) {
			$this->addJsCode(file_get_contents(TPL_DIR . "/Application/Page/js/$name.js"));
		}

		// Javascript code
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/Page/Page.js');

		// Update inline
		if ($service->user->getCurrentUser()->hasPermission('page-update-inline')) {
			$this->addJsLink(CKEDITOR_JS);
			$js->setVars([
				'ID'              => $page['id'],
				'LANG'            => $page['lang'],
				'CSRF'            => $service->user->getCsrfToken(),
				'URL'             => Url::build('api', 'page'),
				'WEB_ROOT'        => WEB_ROOT,
				'IMG_BROWSE'      => Url::build('editor', 'browse', ['id' => $name, 'item' => 'page', 'type' => 'image']),
				'IMG_UPLOAD'      => Url::build('editor', 'upload', ['id' => $name, 'item' => 'page', 'type' => 'image']),
				'FILE_BROWSE'     => Url::build('editor', 'browse', ['id' => $name, 'item' => 'page', 'type' => 'file']),
				'FILE_UPLOAD'     => Url::build('editor', 'upload', ['id' => $name, 'item' => 'page', 'type' => 'file']),
				'IMG_UPLOAD_AJAX' => Url::build('editor', 'upload', ['id' => $name, 'item' => 'page', 'type' => 'image', 'json' => '']),
				'FILE_UPLOAD_AJAX'=> Url::build('editor', 'upload', ['id' => $name, 'item' => 'page', 'type' => 'file', 'json' => '']),
				'CKEDITOR_ROOT'   => CKEDITOR_ROOT,
			]);
			if (defined('IFRAMELY')) {
				$js->setVars([
					'IFRAMELY'     => IFRAMELY,
					'IFRAMELY_KEY' => IFRAMELY_KEY,
				]);
				$js->setBlock('IFRAMELY_BLOCK');
			}
			$js->setBlock('UPDATE_BLOCK');
			$this->setBlock('UPDATE_INLINE_BTN_BLOCK');
		}

		// Update
		if ($service->user->getCurrentUser()->hasPermission('page-update')) {
			$this->setComponent('UPDATE_PAGE_FORM', new Page\Update($name, $lang));
			$this->setBlock('UPDATE_BTN_BLOCK');
			$this->setBlock('UPDATE_MODAL_BLOCK');
		}

		// Delete
		if ($service->user->getCurrentUser()->hasPermission('page-delete')) {
			$deleteForm = new \Sy\Bootstrap\Component\Form\Crud\Delete('page', ['id' => $name, 'lang' => $lang]);
			$deleteForm->setAttribute('id', 'delete-' . $name);
			$this->setComponent('DELETE_PAGE_FORM', $deleteForm);
			$this->setBlock('DELETE_BTN_BLOCK');
			$js->setVars([
				'CONFIRM_DELETE' => $this->_('Are you sure to delete this page?'),
				'DELETE_FORM_ID' => 'delete-' . $name
			]);
			$js->setBlock('DELETE_BLOCK');
		}

		// HTML Code
		if ($service->user->getCurrentUser()->hasPermission('page-html')) {
			$this->setComponent('HTML_FORM', new \Sy\Bootstrap\Component\Page\Html($name, $lang));
			$this->setVar('FORM_HTML_ID', 'form_html_' . $name);
			$js->setVars([
				'CM_HTML_ID' => 'codearea_html_' . $name,
				'GET_URL' => Url::build('api', 'page', ['id' => $name, 'lang' => $lang])
			]);
			$js->setBlock('HTML_BLOCK');
			$this->setBlock('HTML_BTN_BLOCK');
			$this->setBlock('HTML_MODAL_BLOCK');
		}

		// CSS Code
		if ($service->user->getCurrentUser()->hasPermission('page-css')) {
			$this->setComponent('CSS_FORM', new \Sy\Bootstrap\Component\Page\Css($name));
			$this->setVar('FORM_CSS_ID', 'form_css_' . $name);
			$js->setVar('CM_CSS_ID', 'codearea_css_' . $name);
			$js->setBlock('CSS_BLOCK');
			$this->setBlock('CSS_BTN_BLOCK');
			$this->setBlock('CSS_MODAL_BLOCK');
		}

		// JS Code
		if ($service->user->getCurrentUser()->hasPermission('page-js')) {
			$this->setComponent('JS_FORM', new \Sy\Bootstrap\Component\Page\Js($name));
			$this->setVar('FORM_JS_ID', 'form_js_' . $name);
			$js->setVar('CM_JS_ID', 'codearea_js_' . $name);
			$js->setBlock('JS_BLOCK');
			$this->setBlock('JS_BTN_BLOCK');
			$this->setBlock('JS_MODAL_BLOCK');
		}

		// Add javascript code
		$this->addJsCode($js);
	}

	abstract protected function _menu();

}