<?php
namespace Sy\Bootstrap\Component\Message\Received;

class EditForm extends \Sy\Bootstrap\Component\Form {

	public function init() {
		parent::init();

		$this->setAttributes([
			'id'     => 'edit-msg-form',
			'action' => \Sy\Bootstrap\Lib\Url::build('api', 'message/update')
		]);
		$this->addHidden(['name' => 'action', 'value' => 'update']); // to catch action in Api
		$this->addHidden(['name' => 'message_id', 'value' => ''], ['required' => true]);

		// Anti spam check
		$this->addAntiSpamField();

		// Crsf check
		$this->addCsrfField();

		$this->addTextarea([
			'name'        => 'message',
			'required'    => 'required',
			'maxlength'   => 2048,
			'placeholder' => 'Your message'
		], [
			'validator' => function($value) {
				$l = strlen($value);
				if ($l >= 2 and $l <= 2048) return true;
				$m = 'max';
				$n = 2048;
				if ($l <= 2048) {
					$m = 'min';
					$n = 2;
				}
				$this->setError(sprintf($this->_("Text $m length of %d characters"), $n));
				return false;
			}
		]);

		$div = $this->addDiv(['class' => 'clearfix']);

		$this->addButton(
			'Send',
			[
				'type' => 'submit',
				'class'=> 'float-end'
			],
			[
				'size'  => 'sm',
				'color' => 'primary',
				'icon'  => 'fas fa-paper-plane',
			], $div
		);

		$div->addElement(new \Sy\Bootstrap\Component\Form\Picture([
			'name'  => 'picture',
			'size'  => 'sm',
			'title' => 'Choose picture'
		]));
	}

	public function submitAction() {
		$service = \Project\Service\Container::getInstance();
		try {
			$this->validatePost();

			$user = $service->user->getCurrentUser();

			if (!$user->isConnected()) throw new \Sy\Bootstrap\Service\Crud\Exception();

			$picture = $this->post('picture');
			$message = $this->post('message');
			$id = $this->post('message_id');
			if (!empty($picture)) {
				$userId = $user->id;
				$dir = "/photo/message/$id";
				$ldir = "/photo/user/$userId/message/$id";
				if (!file_exists(UPLOAD_DIR . $dir)) {
					mkdir(UPLOAD_DIR . $dir, 0777, true);
				}
				if (!file_exists(UPLOAD_DIR . $ldir)) {
					mkdir(UPLOAD_DIR . $ldir, 0777, true);
				}

				$picture = json_decode($picture, true);
				foreach ($picture as $p) {
					$img = imagecreatefromstring(base64_decode($p['image']));
					$file = uniqid() . '.jpg';
					if (imagejpeg($img, UPLOAD_DIR . "$dir/$file", 90)) {
						@symlink(UPLOAD_DIR . "$dir/$file", UPLOAD_DIR . "$ldir/$file");
						$message .= "\n" . PROJECT_URL . UPLOAD_ROOT . "$dir/$file" . (empty($p['caption']) ? '' : ' [' . str_replace(']', '', $p['caption']) . ']');
					}
				}
			}

			$service->messageReceived->update([
				'id' => $id
			], [
				'message' => $message
			]);
			$result = [
				'status'      => 'ok',
				'message'     => \Sy\Bootstrap\Lib\Str::convert($message),
				'message_raw' => $message
			];
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
