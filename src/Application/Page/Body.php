<?php
namespace Sy\Bootstrap\Application\Page;

use Sy\Bootstrap\Lib\HeadData;
use Sy\Bootstrap\Lib\Url;
use Sy\Component\WebComponent;

class Body extends \Sy\Component\WebComponent {

	/**
	 * @var array
	 */
	private $layoutVars;

	/**
	 * @var array
	 */
	private $contentVars;

	/**
	 * @param string $pageId
	 */
	public function __construct($pageId = null) {
		parent::__construct();

		// Flash message created as soon as possible to handle clear request
		$this->layoutVars = [
			'_FLASH_MESSAGE' => new \Sy\Bootstrap\Component\FlashMessage()
		];
		$this->contentVars = [];
		$this->addTranslator(LANG_DIR);

		$pageId = is_null($pageId) ? $this->get(ACTION_TRIGGER, 'home') : (string) $pageId;
		$this->init($pageId);
	}

	/**
	 * Merge vars with layout vars
	 *
	 * @param array $vars
	 */
	public function setLayoutVars(array $vars) {
		$this->layoutVars = array_merge($this->layoutVars, $vars);
	}

	/**
	 * Merge vars with content vars
	 *
	 * @param array $vars
	 */
	public function setContentVars(array $vars) {
		$this->contentVars = array_merge($this->contentVars, $vars);
	}

	/**
	 * Deprecated use on body method
	 *
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments) {
		if (isset($arguments['LAYOUT'])) {
			$this->setLayoutVars($arguments['LAYOUT']);
		}
		if (isset($arguments['CONTENT'])) {
			$this->setContentVars($arguments['CONTENT']);
		}
		if (isset($arguments['MENU'])) {
			$this->setLayoutVars(['_NAV' => $arguments['MENU']]);
		}
	}

	/**
	 * Executed before every page body method
	 */
	public function all() {
		// Deprecated _menu method
		if (!method_exists($this, '_menu')) return;
		$menu = $this->_menu();
		if (is_null($menu)) return;
		$this->setLayoutVars(['_NAV' => $menu]);
	}

	/**
	 * User connection page
	 */
	public function user_connection() {
		$service = \Project\Service\Container::getInstance();
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
		$service = \Project\Service\Container::getInstance();
		$user = $service->user->retrieve(['email' => $this->get('email')]);
		if (empty($user) or $user['status'] !== 'active' or $this->get('token') !== $user['token']) {
			$this->redirect(WEB_ROOT . '/');
		}
		$this->__call('user-password', ['CONTENT' => [
			'FORM' => new \Sy\Bootstrap\Component\User\ResetPassword($this->get('email'))
		]]);
	}

	/**
	 * Initialyze the body layout and content using page id
	 *
	 * @param string $pageId
	 */
	protected function init($pageId) {
		$name = $pageId;

		// For menu selection
		$_GET[CONTROLLER_TRIGGER] = 'page';
		$_GET[ACTION_TRIGGER] = $pageId;

		// Execute user defined method
		$this->all();
		$method = str_replace('-', '_', $pageId);
		$this->$method();

		// Redirection detected
		if ($_GET[ACTION_TRIGGER] !== $pageId) return;

		// Rel canonical
		if (empty(HeadData::getCanonical())) {
			$get = $_GET;
			unset($get[CONTROLLER_TRIGGER]);
			unset($get[ACTION_TRIGGER]);
			unset($get['sy_language']);
			$url = PROJECT_URL . Url::build('page', $name, $get);
			HeadData::setCanonical($url);
		}

		// Detect language
		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();

		// Retrieve page
		$service = \Project\Service\Container::getInstance();
		$page = $service->page->retrieve(['id' => $name, 'lang' => $lang]);

		// No page found
		if (empty($page) and $name === '404') return;
		if (empty($page)) throw new NotFoundException();

		// Meta title & description
		if (empty(HeadData::getTitle())) HeadData::setTitle($page['title']);
		if (empty(HeadData::getDescription())) HeadData::setDescription($page['description']);

		// Layout file
		$l = file_exists(TPL_DIR . "/Application/Page/layout/$name.html") ? $name : '_default';
		$layout = new WebComponent();
		$layout->setTemplateFile(TPL_DIR . "/Application/Page/layout/$l.html");
		$layout->setTranslators($this->getTranslators());
		$layout->setVars($this->layoutVars);

		// Content file
		$f = TPL_DIR . "/Application/Page/content/$lang/$name.html";
		if (!file_exists($f)) {
			$f = TPL_DIR . "/Application/Page/content/$name.html";
		}
		if (!file_exists($f)) {
			touch($f);
		}
		$content = new WebComponent();
		$content->setTemplateFile($f);
		$content->setTranslators($this->getTranslators());
		$content->setVars($this->contentVars);

		// Set magic slots
		if (defined('MAGIC_VARS')) {
			$content->setVars(MAGIC_VARS);
			$layout->setVars(MAGIC_VARS);
		}

		$layout->setComponent('_CONTENT', $content);
		$this->setTemplateFile(__DIR__ . '/Body.html');
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
			http_response_code(404);
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

		// Code
		if ($service->user->getCurrentUser()->hasPermission('page-code')) {
			// HTML Code
			$this->setComponent('HTML_FORM', new \Sy\Bootstrap\Component\Page\Html($name, $lang));
			$this->setVar('FORM_HTML_ID', 'form_html_' . $name);
			$js->setVars([
				'CM_HTML_ID' => 'codearea_html_' . $name,
			]);

			// CSS Code
			$this->setComponent('CSS_FORM', new \Sy\Bootstrap\Component\Page\Css($name));
			$this->setVar('FORM_CSS_ID', 'form_css_' . $name);
			$js->setVar('CM_CSS_ID', 'codearea_css_' . $name);

			// JS Code
			$this->setComponent('JS_FORM', new \Sy\Bootstrap\Component\Page\Js($name));
			$this->setVar('FORM_JS_ID', 'form_js_' . $name);
			$js->setVar('CM_JS_ID', 'codearea_js_' . $name);

			$js->setBlock('CODE_BLOCK');
			$this->setBlock('CODE_BTN_BLOCK');
			$this->setBlock('CODE_MODAL_BLOCK');
		}

		// Add javascript code
		$this->addJsCode($js);
	}

}