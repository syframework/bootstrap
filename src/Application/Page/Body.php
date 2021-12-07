<?php
namespace Sy\Bootstrap\Application\Page;

use Sy\Bootstrap\Lib\Url;

abstract class Body extends \Sy\Component\WebComponent {

	private $method;

	/**
	 * @var array
	 */
	private $translators;

	public function __construct($method = null) {
		parent::__construct();
		// hack to select the default menu
		if (is_null($this->get(CONTROLLER_TRIGGER))) {
			$_GET[CONTROLLER_TRIGGER] = 'page';
		}
		$this->method = $method;
		$this->translators = array();
		$this->addTranslator(LANG_DIR);
		$this->actionDispatch(ACTION_TRIGGER, 'index');
	}

	public function addTranslator($directory, $type = 'php', $lang = '') {
		parent::addTranslator($directory, $type, $lang);
		$this->translators[] = array(
			'directory' => $directory,
			'type' => $type,
			'lang' => $lang
		);
	}

	public function indexAction() {
		$method = is_null($this->method) ? str_replace('-', '_', $this->get(ACTION_TRIGGER, 'home')) : (string) $this->method;
		// hack to select the default menu
		if ($method === 'home') $_GET[ACTION_TRIGGER] = 'home';
		if (in_array($method, ['__construct', '__call', 'indexAction', '_menu'])) return;
		$this->$method();
	}

	public function __call($name, $arguments) {
		// Flash message created as soon as possible to handle clear request
		$flashMessage = new \Sy\Bootstrap\Component\FlashMessage();
		$arguments['LAYOUT']['_FLASH_MESSAGE'] = $flashMessage;

		$name = str_replace('_', '-', $name);
		$this->setTemplateFile(__DIR__ . '/Body.html');

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

		// No page found
		if (empty($page) and $name === '404') return;
		if (empty($page)) throw new NotFoundException();

		// Show page content
		\Sy\Bootstrap\Lib\HeadData::setTitle($page['title']);
		\Sy\Bootstrap\Lib\HeadData::setDescription($page['description']);

		// Layout file
		$l = file_exists(TPL_DIR . "/Application/Page/layout/$name.html") ? $name : '_default';
		$layout = new \Sy\Component\WebComponent();
		$layout->setTemplateFile(TPL_DIR . "/Application/Page/layout/$l.html");
		foreach ($this->translators as $translator) {
			$layout->addTranslator($translator['directory'], $translator['type'], $translator['lang']);
		}
		if (isset($arguments['LAYOUT'])) {
			foreach ($arguments['LAYOUT'] as $k => $v) {
				$layout->setVar($k, $v);
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
		foreach ($this->translators as $translator) {
			$content->addTranslator($translator['directory'], $translator['type'], $translator['lang']);
		}
		if (isset($arguments['CONTENT'])) {
			foreach ($arguments['CONTENT'] as $k => $v) {
				$content->setVar($k, $v);
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

		// No toolbar for 404 page
		if ($name === '404') {
			header('HTTP/1.0 404 Not Found');
			return;
		}

		// Create
		if ($service->user->getCurrentUser()->hasPermission('page-create')) {
			$form = new \Sy\Bootstrap\Component\Page\Create();
			$form->getField('lang')->setAttribute('value', $lang);
			$this->setComponent('NEW_PAGE_FORM', $form);
			$this->addJsCode("$('#new-page-modal').has('div.alert').modal('show');");
			$this->setBlock('CREATE_BTN_BLOCK');
			$this->setBlock('CREATE_MODAL_BLOCK');
		}

		// Javascript code
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/Body.js');

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
				'GET_URL'         => Url::build('api', 'page', ['id' => $name, 'lang' => $lang])
			]);
			$js->setBlock('UPDATE_BLOCK');
			$this->setBlock('UPDATE_INLINE_BTN_BLOCK');
		}

		// Update
		if ($service->user->getCurrentUser()->hasPermission('page-update')) {
			$this->setComponent('UPDATE_PAGE_FORM', new \Sy\Bootstrap\Component\Page\Update($name, $lang));
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

	/**
	 * Return navigation menu, can return null
	 *
	 * @return \Sy\Bootstrap\Component\Nav\Menu
	 */
	abstract protected function _menu();

	/**
	 * User connection page
	 */
	public function user_connection() {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		if ($service->user->getCurrentUser()->isConnected()) {
			$this->redirect(WEB_ROOT . '/');
		}
		$this->__call('user-connection', ['CONTENT' => [
			'CONNECT_PANEL' => new \Sy\Bootstrap\Component\User\ConnectPanel(),
		]]);
		Url::setReferer(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : WEB_ROOT . '/');
	}

	/**
	 * User reset password page
	 */
	public function user_password() {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		$user = $service->user->retrieve(['email' => $this->get('email')]);
		if (empty($user) or $user['status'] !== 'active' or $this->get('token') !== $user['token']) {
			$this->redirect(WEB_ROOT . '/');
		}
		$this->__call('user-password', ['CONTENT' => [
			'FORM' => new \Sy\Bootstrap\Component\User\ResetPassword($this->get('email'))
		]]);
	}

}

class Exception extends \Exception {}
class NotFoundException extends Exception {}