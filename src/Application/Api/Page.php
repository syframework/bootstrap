<?php
namespace Sy\Bootstrap\Application\Api;

class Page extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$service = \Project\Service\Container::getInstance();
		if (!$service->user->getCurrentUser()->hasPermission('page-update')) {
			throw new \Sy\Bootstrap\Component\Api\ForbiddenException('Permission denied');
		}
	}

	/**
	 * Retrieve page
	 *
	 * @return void
	 */
	public function getAction() {
		$id   = $this->get('id');
		$lang = $this->get('lang');
		if (is_null($id) or is_null($lang)) {
			return $this->requestError();
		}
		$f = TPL_DIR . "/Application/Page/content/$lang/$id.html";
		if (!file_exists($f)) {
			$f = TPL_DIR . "/Application/Page/content/$id.html";
		}
		return $this->ok([
			'status' => 'ok',
			'content' => file_get_contents($f),
		]);
	}

	/**
	 * Update page
	 *
	 * @return void
	 */
	public function postAction() {
		$service = \Project\Service\Container::getInstance();
		try {
			// Update page
			$id      = $this->post('id');
			$lang    = $this->post('lang');
			$content = $this->post('content');
			$csrf    = $this->post('csrf');
			if ($csrf !== $service->user->getCsrfToken()) {
				return $this->requestError([
					'status'  => 'ko',
					'message' => $this->_('You have taken too long to submit the form please try again'),
					'csrf'    => $service->user->getCsrfToken(),
				]);
			}
			if (is_null($id) or is_null($lang) or is_null($content)) $this->requestError();
			// Save a version in t_page_history
			$service->pageHistory->change([
				'user_id'      => $service->user->getCurrentUser()->id,
				'page_id'      => $id,
				'page_lang'    => $lang,
				'page_crc32'   => crc32($content),
				'page_content' => $content,
			], [
				'user_id'    => $service->user->getCurrentUser()->id,
				'updated_at' => date('Y-m-d H:i:s'),
			]);

			if (!file_exists(TPL_DIR . "/Application/Page/content/$lang")) {
				if (!mkdir(TPL_DIR . "/Application/Page/content/$lang", 0777, true)) {
					return $this->serverError([
						'status'  => 'ko',
						'message' => $this->_('File write error'),
					]);
				};
			}

			if (file_put_contents(TPL_DIR . "/Application/Page/content/$lang/$id.html", $content) === false) {
				return $this->serverError([
					'status'  => 'ko',
					'message' => $this->_('File write error'),
				]);
			} else {
				return $this->ok([
					'status' => 'ok',
				]);
			}
		} catch (\Sy\Db\MySql\Exception $e) {
			return $this->serverError([
				'status' => 'ko',
				'message' => $this->_('Database error'),
			]);
		}
	}

}