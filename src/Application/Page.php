<?php
namespace Sy\Bootstrap\Application;

use Sy\Bootstrap\Component\Page\Code;
use Sy\Bootstrap\Lib\HeadData;
use Sy\Bootstrap\Lib\Url;
use Sy\Component\WebComponent;

abstract class Page extends \Sy\Component\Html\Page {

	/**
	 * @var string
	 */
	private $pageId;

	/**
	 * @var string
	 */
	private $layout;

	/**
	 * @var array
	 */
	private $layoutVars;

	/**
	 * @var array
	 */
	private $contentVars;

	/**
	 * Method called before init
	 */
	abstract protected function preInit();

	/**
	 * Method called after init
	 */
	abstract protected function postInit();

	/**
	 * @param string|null $pageId
	 */
	public function __construct($pageId = null) {
		parent::__construct();

		// Flash message created as soon as possible to handle clear request
		$this->layoutVars = [
			'_FLASH_MESSAGE' => new \Sy\Bootstrap\Component\FlashMessage(),
		];
		$this->contentVars = [];
		$this->layout = '';
		$this->pageId = empty($pageId) ? $this->get(ACTION_TRIGGER, 'home') : (string)$pageId;
		$_REQUEST[ACTION_TRIGGER] = $this->pageId;

		$this->mount(function () {
			$this->init();
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() {
		try {
			return parent::render();
		} catch (\Sy\Bootstrap\Application\Page\NotFoundException $e) {
			$this->copy('404');
		}
	}

	/**
	 * User connection page
	 */
	public function userConnectionAction() {
		$service = \Project\Service\Container::getInstance();
		if ($service->user->getCurrentUser()->isConnected()) {
			$this->redirect(WEB_ROOT . '/');
		}
		$this->setContentVars([
			'CONNECT_PANEL' => new \Sy\Bootstrap\Component\User\ConnectPanel(),
		]);
	}

	/**
	 * User reset password page
	 */
	public function userPasswordAction() {
		$service = \Project\Service\Container::getInstance();
		$user = $service->user->retrieve(['email' => $this->get('email')]);
		if (empty($user) or $user['status'] !== 'active' or $this->get('token') !== $user['token']) {
			$this->redirect(WEB_ROOT . '/');
		}
		$this->setContentVars([
			'FORM' => new \Sy\Bootstrap\Component\User\ResetPassword($this->get('email')),
		]);
	}

	/**
	 * Change the page action id and execute the new action method
	 *
	 * @param string $pageId
	 */
	protected function copy($pageId) {
		$this->pageId = $pageId;
		$_REQUEST[ACTION_TRIGGER] = $pageId;
		$this->actionDispatch(ACTION_TRIGGER);
	}

	/**
	 * Set the layout name
	 *
	 * @param string $layout
	 */
	protected function setLayout($layout) {
		$this->layout = $layout;
	}

	/**
	 * Merge vars with layout vars
	 *
	 * @param array $vars
	 */
	protected function setLayoutVars(array $vars) {
		$this->layoutVars = array_merge($this->layoutVars, $vars);
	}

	/**
	 * Merge vars with content vars
	 *
	 * @param array $vars
	 */
	protected function setContentVars(array $vars) {
		$this->contentVars = array_merge($this->contentVars, $vars);
	}

	private function init() {
		$this->addTranslator(LANG_DIR);

		$this->preInit();

		// Lang
		$service = \Project\Service\Container::getInstance();
		$lang = $service->lang->getLang();
		HeadData::setHtmlAttribute('lang', $lang);

		// JSON-LD
		$this->setJsonLd(HeadData::getJsonLd());

		// Html & Body attributes
		$this->setHtmlAttributes(HeadData::getHtmlAttributes());
		$this->setBodyAttributes(HeadData::getBodyAttributes());

		// Title, description and body
		$this->addBody($this->body());
		$this->setTitle($this->_(HeadData::getTitle()) . ' - ' . PROJECT);
		$this->setDescription($this->_(HeadData::getDescription()));

		// Base
		$this->setBase(...HeadData::getBase());

		// Meta
		foreach (HeadData::getMeta() as $meta) {
			$this->setMeta($meta['name'], $meta['content'], $meta['http-equiv']);
		}

		// Canonical
		$canonical = HeadData::getCanonical();
		if (!empty($canonical)) {
			$this->addLink(['rel' => 'canonical', 'href' => $canonical]);
		}

		// Activate the web debug tool bar
		if (getenv('ENVIRONMENT') === 'dev') {
			$this->enableDebugBar();
		}

		$this->postInit();
	}

	/**
	 * Initialyze the body layout and content using page id
	 */
	private function body() {
		// Execute user defined action method
		try {
			$this->actionDispatch(ACTION_TRIGGER);
		} catch (\Sy\Bootstrap\Application\Page\NotFoundException $e) {
			$this->copy('404');
		}

		$name = $this->pageId;

		// For menu selection
		$_GET[CONTROLLER_TRIGGER] = 'page';
		$_GET[ACTION_TRIGGER] = $name;

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
		$service = \Project\Service\Container::getInstance();
		$lang = $service->lang->getLang();

		// Retrieve page
		$service = \Project\Service\Container::getInstance();
		$page = $service->page->retrieve(['id' => $name]);

		// No page found
		if (empty($page)) $name = '404';

		// Layout file
		$layoutName = empty($this->layout) ? $name : $this->layout;
		$l = file_exists(TPL_DIR . "/Application/Page/layout/$layoutName.html") ? $layoutName : '_default';
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
			chmod($f, 0666);
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
		$body = new WebComponent();
		$body->setTemplateFile(__DIR__ . '/Page/Body.html');
		$body->setComponent('LAYOUT', $layout);
		$body->setBlock('LAYOUT_BLOCK');

		// Page CSS and JS
		if (file_exists(TPL_DIR . "/Application/Page/css/$name.css")) {
			$body->addCssCode(file_get_contents(TPL_DIR . "/Application/Page/css/$name.css"));
		}
		if (file_exists(TPL_DIR . "/Application/Page/js/$name.js")) {
			$body->addJsCode(file_get_contents(TPL_DIR . "/Application/Page/js/$name.js"));
		}

		// No toolbar for 404 page
		if ($name === '404') {
			HeadData::setTitle($this->_('Page not found'));
			http_response_code(404);
			return $body;
		}

		// Meta title & description
		if (empty(HeadData::getTitle())) HeadData::setTitle($this->_($page['title']));
		if (empty(HeadData::getDescription())) HeadData::setDescription($this->_($page['description']));

		// Create
		if ($service->user->getCurrentUser()->hasPermission('page-create')) {
			$form = new \Sy\Bootstrap\Component\Page\Create();
			$form->initialize();
			$body->setComponent('NEW_PAGE_FORM', $form);
			$body->setBlock('CREATE_BTN_BLOCK');
			$body->setBlock('CREATE_MODAL_BLOCK');
		}

		// Javascript code
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/Page/Body.js');

		// Update
		if ($service->user->getCurrentUser()->hasPermission('page-update')) {
			$body->setComponent('UPDATE_PAGE_FORM', new \Sy\Bootstrap\Component\Page\Update($name));
			$body->setBlock('UPDATE_BTN_BLOCK');
			$body->setBlock('UPDATE_MODAL_BLOCK');
		}

		// Delete
		if ($service->user->getCurrentUser()->hasPermission('page-delete')) {
			$deleteForm = new \Sy\Bootstrap\Component\Form\Crud\Delete('page', ['id' => $name]);
			$deleteForm->setAttribute('id', 'delete-' . $name);
			$body->setComponent('DELETE_PAGE_FORM', $deleteForm);
			$body->setBlock('DELETE_BTN_BLOCK');
			$js->setVars([
				'CONFIRM_DELETE' => $this->_('Are you sure to delete this page?'),
				'DELETE_FORM_ID' => 'delete-' . $name,
			]);
			$js->setBlock('DELETE_BLOCK');
		}

		// Code
		if ($service->user->getCurrentUser()->hasPermission('page-code')) {
			$body->setVars([
				'CODE_FORM'    => new Code($name, $lang),
				'CODE_FORM_ID' => 'code_form_' . $name,
			]);

			$js->setVars([
				'ID' => $page['id'],
				'GET_URL' => Url::build('api', 'page', ['id' => $name, 'lang' => $lang]),
			]);
			$js->setBlock('CODE_BLOCK');
			$body->setBlock('CODE_BTN_BLOCK');
			$body->setBlock('CODE_MODAL_BLOCK');
		}

		// Add javascript code
		$body->addJsCode($js, ['position' => WebComponent::JS_TOP]);

		return $body;
	}

}