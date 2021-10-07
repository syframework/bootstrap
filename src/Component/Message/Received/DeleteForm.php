<?php
namespace Sy\Bootstrap\Component\Message\Received;

class DeleteForm extends \Sy\Bootstrap\Component\Form {

	/**
	 * @var int
	 */
	private $messageId;

	public function __construct($messageId) {
		$this->messageId = $messageId;
		parent::__construct();
	}

	public function init() {
		parent::init();

		$this->setAttributes([
			'class'            => 'message-delete-form',
			'data-message-id'  => $this->messageId,
			'data-message-del' => $this->_('Are you sure to delete?'),
			'action'           => \Sy\Bootstrap\Lib\Url::build('api', 'message/delete')
		]);

		$this->addHidden(['name' => 'action', 'value' => 'delete']); // to catch action in Api
		$this->addHidden(['name' => 'message_id', 'value' => $this->messageId, 'required' => 'required']);

		// Crsf check
		$this->addCsrfField();

		$this->addButton('',
			['type' => 'submit', 'title' => $this->_('Delete'), 'data-bs-title' => $this->_('Delete'), 'data-bs-container' => 'body'],
			['color' => 'danger', 'size' => 'sm', 'icon' => 'fas fa-trash-alt fa-fw']
		);
	}

	public function submitAction() {
		$service = \Project\Service\Container::getInstance();
		try {
			$this->validatePost();
			$service->messageReceived->delete(['id' => $this->messageId]);
			$result = ['status' => 'ok', 'message' => $this->_('Deleted successfully')];
			echo json_encode($result);
		} catch(\Sy\Bootstrap\Component\Form\CsrfException $e) {
			$this->logWarning($e);
			$result = ['status' => 'ko', 'message' => $e->getMessage(), 'csrf' => $service->user->getCsrfToken()];
			echo json_encode($result);
		} catch(\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$result = ['status' => 'ko', 'message' => is_null($this->getOption('error')) ? $this->_('Please fill the form correctly') : $this->getOption('error')];
			echo json_encode($result);
		} catch(\Sy\Bootstrap\Service\Crud\Exception $e) {
			$this->logWarning($e);
			$result = ['status' => 'ko', 'message' => $this->_('Error')];
			echo json_encode($result);
		}
		exit();
	}

}
