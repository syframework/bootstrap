<?php
namespace Sy\Bootstrap\Service;

use Sy\Bootstrap\Lib\Upload;
use Sy\Bootstrap\Lib\Str;
use Sy\Event\Event;

/**
 * @method array getPermissions(int $userId) Retrieve user permissions
 * @method array getSettings(int $userId) Retrieve user settings
 * @method void setSetting(int $userId, string $key, mixed $value) Set user setting
 */
class User extends Crud {

	/**
	 * Current user
	 *
	 * @var \Sy\Bootstrap\Model\User
	 */
	protected $currentUser;

	public function __construct() {
		parent::__construct('user');
		if (!session_id()) {
			ini_set('session.cookie_httponly', true);
			ini_set('session.use_strict_mode', true);
			session_start();
		}
		$this->autoSignIn();
	}

	/**
	 * Sign in
	 *
	 * @param  string $email
	 * @param  string $password
	 * @param  bool $remember
	 * @throws User\SignInException
	 * @throws User\ActivateAccountException
	 */
	public function signIn($email, $password, $remember = true) {
		$user = $this->retrieve(['email' => $email]);

		// No user
		if (empty($user)) throw new User\SignInException();

		// User password
		$pass = $user['password'];

		// Inactive user
		if ($user['status'] === 'inactive') {
			$service = \Project\Service\Container::getInstance();
			// Activate account if password is good
			if (!empty($pass) and $this->passwordVerify($password, $pass)) {
				$this->update(['email' => $email], ['status' => 'active', 'token' => '']);
				// Dispatch an event after user activation
				$service->event->dispatch(new Event('user.activated', ['email' => $email]));
			} else {
				$pwd = Str::generatePassword();
				$this->update(['email' => $email], ['password' => password_hash($pwd, PASSWORD_DEFAULT)]);
				$service->mail->sendWelcome($user['email'], $pwd, $user['token']);
				throw new User\ActivateAccountException();
			}
		}

		// Wrong password
		if (empty($pass) or !$this->passwordVerify($password, $pass)) throw new User\SignInException();

		// Session
		session_destroy(); // Security: renew PHPSESSID on sign in
		session_start();
		$fingerprint = preg_replace("/[^a-zA-Z]/", '', $_SERVER['HTTP_USER_AGENT']);
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['fingerprint'] = $fingerprint;
		if ($remember) {
			$encryptedId = $this->encrypt($user['id']);
			setcookie('_x', $encryptedId, time() + 60 * 60 * 24 * 365, WEB_ROOT . '/');
			setcookie('_y', sha1($pass . $fingerprint), time() + 60 * 60 * 24 * 365, WEB_ROOT . '/');
		} else {
			setcookie('_x', '', 0);
			setcookie('_y', '', 0);
		}

		// Update last connection datetime
		$this->update(['id' => $user['id']], ['last_connection_at' => date('Y-m-d H:i:s')]);

		// Reset current user
		$this->currentUser = null;
	}

	/**
	 * Sign up
	 *
	 * @param  string $email
	 * @param  string|null $password Optionnal password
	 * @throws User\SignUpException
	 */
	public function signUp($email, $password = null) {
		try {
			$this->transaction(function() use($email, $password) {
				// Generate nickname
				$name = Str::generateNicknameFromEmail($email);

				$password = is_null($password) ? Str::generatePassword() : $password; // Generate a password
				$token = sha1(uniqid());
				$service = Container::getInstance();
				$userId = $this->create([
					'firstname' => $name,
					'email'    => $email,
					'language' => $service->lang->getLang(),
					'password' => password_hash($password, PASSWORD_DEFAULT),
					'token'    => $token,
					'ip'       => sprintf("%u", ip2long($_SERVER['REMOTE_ADDR'])),
				]);
				$service = \Project\Service\Container::getInstance();
				$service->mail->sendWelcome($email, $password, $token);
				return $userId;
			});
		} catch (\Sy\Db\MySql\DuplicateEntryException $e) {
			$user = $this->retrieve(['email' => $email]);
			if ($user['status'] === 'inactive') {
				$pwd = Str::generatePassword();
				$this->update(['email' => $email], ['password' => password_hash($pwd, PASSWORD_DEFAULT)]);
				$service = \Project\Service\Container::getInstance();
				$service->mail->sendWelcome($user['email'], $pwd, $user['token']);
				throw new User\ActivateAccountException('Account not activated', 0, $e);
			} else {
				throw new User\AccountExistException('Account already exists', 0, $e);
			}
		} catch (\Sy\Db\MySql\Exception $e) {
			throw new User\SignUpException('Database error', 0, $e);
		} catch (\Sy\Mail\Exception $e) {
			throw new User\SignUpException('Mail error', 0, $e);
		}
	}

	/**
	 * Sign out
	 */
	public function signOut() {
		// Removes session data
		$_SESSION = array();

		// Removes session cookie
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time() - 42000, WEB_ROOT . '/');
		}

		setcookie('_x', '', 0, WEB_ROOT . '/');
		setcookie('_y', '', 0, WEB_ROOT . '/');

		// Destroys session
		session_destroy();
	}

	/**
	 * Activate a user account after sign up
	 *
	 * @param  string $email
	 * @param  string $token
	 * @throws User\ActivateAccountException
	 */
	public function activate($email, $token) {
		$u = $this->retrieve(['email' => $email]);
		if ($u['status'] === 'active') throw new User\ActivateAccountException();
		if (!isset($u['token']) or $u['token'] !== $token) throw new User\ActivateAccountException();
		$this->update(['email' => $email], ['status' => 'active', 'token' => '']);
		// Dispatch an event after user activation
		$service = \Project\Service\Container::getInstance();
		$service->event->dispatch(new Event('user.activated', ['email' => $email]));
	}

	/**
	 * Delete a user account after a report of unwanted sign up
	 *
	 * @param  string $email
	 * @param  string $token
	 * @throws User\ActivateAccountException
	 */
	public function report($email, $token) {
		$u = $this->retrieve(['email' => $email]);
		if (!isset($u['token']) or $u['token'] !== $token) throw new User\ActivateAccountException();
		$this->delete(['email' => $email]);
	}

	/**
	 * Request for a new password
	 *
	 * @param  string $email
	 * @throws User\Exception
	 * @throws User\ActivateAccountException
	 */
	public function forgetPassword($email) {
		try {
			$user = $this->retrieve(['email' => $email]);
			$service = \Project\Service\Container::getInstance();

			// No user
			if (empty($user)) throw new User\Exception('User not found');

			// Inactive user
			if ($user['status'] === 'inactive') {
				$pwd = Str::generatePassword();
				$this->update(['email' => $email], ['password' => password_hash($pwd, PASSWORD_DEFAULT)]);
				$service->mail->sendWelcome($user['email'], $pwd, $user['token']);
				throw new User\ActivateAccountException();
			}

			$token = sha1(uniqid());
			$this->update(['email' => $email], ['token' => $token]);
			$service->mail->sendForgetPassword($email, $token);
		} catch (\Sy\Mail\Exception $e) {
			throw new User\Exception('Mail error');
		}
	}

	/**
	 * @return \Sy\Bootstrap\Model\User
	 */
	public function getCurrentUser() {
		if (!isset($this->currentUser)) {
			$userModelClass = '\\Project\\Model\\User';
			if (class_exists($userModelClass)) {
				$this->currentUser = new $userModelClass(\Sy\Http::session('user_id', 0));
			} else {
				$this->currentUser = new \Sy\Bootstrap\Model\User(\Sy\Http::session('user_id', 0));
			}
		}
		return $this->currentUser;
	}

	/**
	 * Set a token in session and return it
	 *
	 * @return string
	 */
	public function getCsrfToken() {
		if (!isset($_SESSION['csrfToken'])) {
			$_SESSION['csrfToken'] = md5(uniqid(rand(), true));
		}
		return $_SESSION['csrfToken'];
	}

	/**
	 * @param string $language
	 */
	public function setLanguage($language) {
		$service = Container::getInstance();
		$service->lang->setLang($language);
	}

	/**
	 * @param  string $password
	 * @param  string $hash
	 * @return bool
	 */
	public function passwordVerify($password, $hash) {
		return password_verify($password, $hash);
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(array $pk) {
		$res = $this->retrieve($pk);
		parent::delete($pk);

		if (empty($res)) return;
		// Dispatch deleted user event
		$service = \Project\Service\Container::getInstance();
		$service->event->dispatch(new Event('user.deleted', $res));

		// Delete uploaded pictures
		if (empty($res['email'])) return;
		$md5 = md5(strtolower(trim($res['email'])));
		Upload::delete(UPLOAD_DIR . "/avatar/$md5.webp");
	}

	/**
	 * Auto sign in when user remember is true
	 */
	protected function autoSignIn() {
		$fingerprint = isset($_SERVER['HTTP_USER_AGENT']) ? preg_replace("/[^a-zA-Z]/", '', $_SERVER['HTTP_USER_AGENT']) : 'bot';

		if (!is_null(\Sy\Http::session('user_id'))) {
			if (\Sy\Http::session('fingerprint') === $fingerprint) {
				return; // Already connected
			}
		}
		if (empty($_COOKIE['_x'])) return;
		if (empty($_COOKIE['_y'])) return;

		$decryptedId = $this->decrypt($_COOKIE['_x']);
		$user = $this->retrieve(['id' => $decryptedId]);
		if (empty($user)) return;

		$hash = sha1($user['password'] . $fingerprint);
		if ($hash === $_COOKIE['_y']) {
			session_destroy(); // Security: renew PHPSESSID on sign in
			session_start();
			setcookie('_x', $_COOKIE['_x'], time() + 60 * 60 * 24 * 365, WEB_ROOT . '/');
			setcookie('_y', $hash, time() + 60 * 60 * 24 * 365, WEB_ROOT . '/');
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['fingerprint'] = $fingerprint;
		}
		// Update last connection datetime
		$this->update(['id' => $user['id']], ['last_connection_at' => date('Y-m-d H:i:s')]);
	}

	/**
	 * @param  string $text Input text to encrypt
	 * @return string Encrypted text
	 */
	protected function encrypt($text) {
		$key = str_split(md5(defined('PROJECT_KEY') ? PROJECT_KEY : ''), 16);
		return base64_encode(openssl_encrypt($text, 'AES-256-CBC', $key[0], OPENSSL_RAW_DATA, $key[1]));
	}

	/**
	 * @param  string $text Input text to decrypt
	 * @return string Decrypted text
	 */
	protected function decrypt($text) {
		$key = str_split(md5(defined('PROJECT_KEY') ? PROJECT_KEY : ''), 16);
		return openssl_decrypt(base64_decode($text), 'AES-256-CBC', $key[0], OPENSSL_RAW_DATA, $key[1]);
	}

}