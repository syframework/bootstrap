<?php
namespace Sy\Bootstrap\Service;

use Sy\Bootstrap\Lib\Upload;
use Sy\Bootstrap\Lib\Str;

/**
 * @method array getPermissions(int $userId) Retrieve user permissions
 * @method array retrieveFollowing(int $userId, int $offset) Retrieve user following
 * @method array retrieveFollower(int $userId, int $offset) Retrieve user followers
 * @method array retrieveSuggestion(double $lat, double $lng, int $userId) Retrieve suggestion of users near to a point
 */
class User extends Crud {

	/**
	 * Current user
	 *
	 * @var Model\User
	 */
	private $currentUser;

	public function __construct() {
		parent::__construct('user');
		ini_set('session.cookie_httponly', true);
		ini_set('session.use_strict_mode', true);
		if (!session_id()) session_start();
		$this->autoSignIn();
	}

	/**
	 * Sign in
	 *
	 * @param string $email
	 * @param string $password
	 * @param bool $remember
	 * @throws User\SignInException
	 * @throws User\ActivateAccountException
	 */
	public function signIn($email, $password, $remember = true) {
		$user = $this->retrieve(['email' => $email]);

		// No user
		if (empty($user)) throw new User\SignInException;

		// User password
		$pass = $user['password'];
		$algo = isset($user['algo']) ? $user['algo'] : 'bcrypt';

		// Inactive user
		if ($user['status'] === 'inactive') {
			// Activate account if password is good
			if (!empty($pass) and $this->passwordVerify($password, $pass, $algo)) {
				$this->update(['email' => $email], ['status' => 'active', 'token' => '']);
			} else {
				$pwd = Str::generatePassword();
				$this->update(['email' => $email], ['password' => password_hash($pwd, PASSWORD_DEFAULT), 'algo' => 'bcrypt']);
				$service = \Sy\Bootstrap\Service\Container::getInstance();
				$service->mail->sendWelcome($user['email'], $pwd, $user['token']);
				throw new User\ActivateAccountException;
			}
		}

		// Wrong password
		if (empty($pass) or !$this->passwordVerify($password, $pass, $algo)) throw new User\SignInException;

		// Upgrade algo from sha1 to bcrypt
		if ($algo === 'sha1') {
			$pass = password_hash($password, PASSWORD_DEFAULT);
			$this->update(['id' => $user['id']], ['password' => $pass, 'algo' => 'bcrypt']);
		}

		// Session
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
	 * @param string $email
	 * @throws User\SignUpException
	 */
	public function signUp($email, $password = null) {
		try {
			$this->getDbCrud()->beginTransaction();

			// Generate a unique nickname
			$name = Str::generateNicknameFromEmail($email);
			while(!empty($this->retrieve(['firstname' => $name]))) {
				$name = Str::generateNickname();
			}

			$password = is_null($password) ? Str::generatePassword() : $password; // Generate a password
			$token = sha1(uniqid());
			$this->create([
				'firstname'=> $name,
				'email'    => $email,
				'language' => \Sy\Translate\LangDetector::getInstance(LANG)->getLang(),
				'password' => password_hash($password, PASSWORD_DEFAULT),
				'algo'     => 'bcrypt',
				'token'    => $token,
				'ip'       => sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']))
			]);
			$service = \Sy\Bootstrap\Service\Container::getInstance();
			$service->mail->sendWelcome($email, $password, $token);
			$this->getDbCrud()->commit();
		} catch(\Sy\Bootstrap\Service\Crud\Exception $e) {
			$this->logWarning($e);
			$this->getDbCrud()->rollBack();
			throw new User\SignUpException('Database error');
		} catch(\Sy\Mail\Exception $e) {
			$this->logWarning($e);
			$this->getDbCrud()->rollBack();
			throw new User\SignUpException('Mail error');
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
	 * @param string $email
	 * @param string $token
	 * @throws User\ActivateAccountException
	 */
	public function activate($email, $token) {
		$u = $this->retrieve(['email' => $email]);
		if ($u['status'] === 'active') throw new User\ActivateAccountException;
		if (!isset($u['token']) or $u['token'] !== $token) throw new User\ActivateAccountException;
		$this->update(['email' => $email], ['status' => 'active', 'token' => '']);
	}

	/**
	 * Delete a user account after a report of unwanted sign up
	 *
	 * @param string $email
	 * @param string $token
	 * @throws User\ActivateAccountException
	 */
	public function report($email, $token) {
		$u = $this->retrieve(['email' => $email]);
		if (!isset($u['token']) or $u['token'] !== $token) throw new User\ActivateAccountException;
		$this->delete(['email' => $email]);
	}

	/**
	 * Request for a new password
	 *
	 * @param string $email
	 * @throws User\Exception
	 * @throws User\ActivateAccountException
	 */
	public function forgetPassword($email) {
		try {
			$user = $this->retrieve(['email' => $email]);
			$service = \Sy\Bootstrap\Service\Container::getInstance();

			// No user
			if (empty($user)) throw new User\Exception('User not found');

			// Inactive user
			if ($user['status'] === 'inactive') {
				$pwd = Str::generatePassword();
				$this->update(['email' => $email], ['password' => password_hash($pwd, PASSWORD_DEFAULT), 'algo' => 'bcrypt']);
				$service->mail->sendWelcome($user['email'], $pwd, $user['token']);
				throw new User\ActivateAccountException;
			}

			$token = sha1(uniqid());
			$this->update(['email' => $email], ['token' => $token]);
			$service->mail->sendForgetPassword($email, $token);
		} catch(\Sy\Mail\Exception $e) {
			$this->logWarning($e);
			throw new User\Exception('Mail error');
		}
	}

	/**
	 * @return \Sy\Bootstrap\Model\User
	 */
	public function getCurrentUser() {
		if (!isset($this->currentUser)) {
			$this->currentUser = new \Sy\Bootstrap\Model\User(\Sy\Http::session('user_id', 0));
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
		if (!in_array($language, array_keys(LANGS))) $language = LANG;
		setcookie('sy_language', $language, time() + 60 * 60 * 24 * 365, WEB_ROOT . '/');
		$_COOKIE['sy_language'] = $language;
		$_SESSION['sy_language'] = $language;
		$_GET['sy_language'] = $language;
		\Sy\Translate\LangDetector::getInstance(LANG)->setLang($language);
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
		$hash = sha1($user['password'] . $fingerprint);
		if ($hash === $_COOKIE['_y']) {
			setcookie('_x', $_COOKIE['_x'], time() + 60 * 60 * 24 * 365, WEB_ROOT . '/');
			setcookie('_y', $hash, time() + 60 * 60 * 24 * 365, WEB_ROOT . '/');
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['fingerprint'] = $fingerprint;
		}
		// Update last connection datetime
		$this->update(['id' => $user['id']], ['last_connection_at' => date('Y-m-d H:i:s')]);
	}

	/**
	 * @param string $text Input text to encrypt
	 * @return string Encrypted text
	 */
	protected function encrypt($text) {
		return base64_encode(openssl_encrypt($text, 'AES-256-CBC', 'AAROLD1234567890', 0, '1234567890DLORAA'));
	}

	/**
	 * @param string $text Input text to decrypt
	 * @return string Decrypted text
	 */
	protected function decrypt($text) {
		return openssl_decrypt(base64_decode($text), 'AES-256-CBC', 'AAROLD1234567890', 0, '1234567890DLORAA');
	}

	/**
	 * @param type $password
	 * @param type $hash
	 * @param type $algo
	 * @return bool
	 */
	public function passwordVerify($password, $hash, $algo) {
		switch ($algo) {
			case 'sha1':
				return sha1($password) === $hash;

			case 'bcrypt':
				return password_verify($password, $hash);
		}
	}

	public function delete(array $pk) {
		$res = $this->retrieve($pk);
		parent::delete($pk);

		// Delete uploaded pictures
		if (empty($res['id'])) return;
		Upload::delete(UPLOAD_DIR . '/photo/user/' . $res['id']);
		Upload::delete(UPLOAD_DIR . '/avatar/' . $res['id'] . '.png');
	}
}

namespace Sy\Bootstrap\Service\User;

class Exception extends \Exception {}

class SignInException extends Exception {}

class SignUpException extends Exception {}

class ActivateAccountException extends Exception {}

class AccountNotExistException extends Exception {}