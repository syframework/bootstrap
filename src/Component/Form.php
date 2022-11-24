<?php
namespace Sy\Bootstrap\Component;

abstract class Form extends \Sy\Component\Html\Form {

	public function init() {
		$this->addTranslator(LANG_DIR);

		$this->setOptions([
			'error-class'   => 'alert alert-danger',
			'success-class' => 'alert alert-success',
		]);
	}

	public function validatePost() {
		$_POST = $this->trim($_POST);
		parent::validate($_POST);
	}

	public function setSuccess($message, $redirection = null, $timeout = 3500) {
		$this->setFlashMessage($message, 'success', $redirection, $timeout);
	}

	public function setDanger($message, $redirection = null, $timeout = 3500) {
		$this->setFlashMessage($message, 'danger', $redirection, $timeout);
	}

	/**
	 * @param string or array $message if array possible keys; 'title', 'message'
	 * @param string $type 'success', 'info', 'warning', 'danger'
	 * @param string $redirection url
	 * @param int $timeout 0 for static message
	 */
	public function setFlashMessage($message, $type, $redirection = null, $timeout = 3500) {
		\Sy\Bootstrap\Lib\FlashMessage::setMessage($message, $type, $timeout);
		$this->redirect(is_null($redirection) ? $_SERVER['REQUEST_URI'] : $redirection);
	}

	public function addAntiSpamField() {
		$f = new \Sy\Component\Html\Form\FieldContainer('div');
		$f->setAttribute('style', 'height:0;overflow:hidden');
		$f->addTextInput(
			['name' => 'sy_firstname', 'autocomplete' => 'true'],
			['validator' => function($p) {
				return is_null($p);
			}]
		);
		$f->addTextInput(
			['name' => 'sy_lastname', 'value' => 'Your lastname', 'required' => 'required', 'autocomplete' => 'true'],
			['validator' => function($p) {
				return $p === 'Your lastname';
			}]
		);
		return $this->addElement($f);
	}

	public function addCsrfField() {
		$service = \Project\Service\Container::getInstance();
		$token = $service->user->getCsrfToken();
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/Form/Form.js');
		$js->setVar('URL', \Sy\Bootstrap\Lib\Url::build('api', 'csrf'));
		$this->addJsCode($js->__toString());
		return $this->addHidden(
			['name' => '__csrf', 'value' => $token],
			[
				'validator' => function($v) use($token) {
					if ($v !== $token) {
						throw new Form\CsrfException($this->_('You have taken too long to submit the form please try again'));
					} else {
						return true;
					}
				},
			]
		);
	}

	/**
	 * Add a text input with some bootstrap options
	 *
	 * @param  array $attributes
	 * @param  array $options label, help, size (sm or lg), addon-before, addon-after
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return \Sy\Component\Html\Form\TextFillableInput
	 */
	public function addTextInput(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('text', $attributes, $options, $container);
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addPassword(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('password', $attributes, $options, $container);
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addEmail(array $attributes = [], array $options = [], $container = null) {
		$this->addJsLink(MAILCHECK_JS);
		$this->addJsCode(__DIR__ . '/Form/Email.js');
		$attributes['data-help'] = isset($options['help']) ? $this->_($options['help']) : '';
		$attributes['data-error'] = $this->_('Did you mean [EMAIL]?');
		$options['help'] = isset($options['help']) ? $options['help'] : '';
		$input = $this->addInput('email', $attributes, $options, $container);
		$input->addValidator('Sy\\Component\\Html\\Form\\email');
		return $input;
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addTel(array $attributes = [], array $options = [], $container = null) {
		// Add Intl tel input JS and CSS
		$this->addCssCode('.iti{width:100%}');
		$this->addCssLink(INTLTELINPUT_CSS);
		$this->addJsLink(INTLTELINPUT_JS);
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/Form/Tel.js');
		$js->setVars([
			'INTLTELINPUT_UTILS_JS' => INTLTELINPUT_UTILS_JS,
			'WEB_ROOT' => WEB_ROOT,
			'TOP_COUNTRIES' => defined('INTLTELINPUT_TOP_COUNTRIES') ? '"' . implode('","', INTLTELINPUT_TOP_COUNTRIES) . '"' : '',
		]);
		$this->addJsCode($js->__toString());

		$attributes['data-name'] = $attributes['name'];
		$attributes['data-error'] = $this->_('Invalid phone number');
		$attributes['data-help'] = isset($options['help']) ? $this->_($options['help']) : '';
		$options['help'] = isset($options['help']) ? $options['help'] : '';

		// Add a hidden text input
		$a = ['name' => $attributes['name'], 'class' => 'tel-hidden-input visually-hidden'];
		if (isset($attributes['required'])) {
			$a['required'] = $attributes['required'];
		}
		if (isset($attributes['value'])) {
			$a['value'] = $attributes['value'];
		}
		$attributes['name'] = $attributes['name'] . '-raw';

		// Add a visible tel input
		$input = $this->addInput('tel', $attributes, $options, $container);
		$input->getParent()->addTextInput($a);
		return $input;
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addUrl(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('url', $attributes, $options, $container);
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addDate(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('date', $attributes, $options, $container);
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addDateTime(array $attributes = [], array $options = [], $container = null) {
		list('name' => $name, 'value' => $value) = $attributes;
		unset($attributes['name'], $attributes['value']);
		$attributes['class'] = 'datetime-utc';

		// Value must be in UTC in format "yyyy-mm-ddThh:mmZ"
		if (!empty($value)) {
			$date = new \Sy\Bootstrap\Lib\Date($value);
			$value = $date->f("yyyy-MM-dd'T'HH:mm'Z'");
		}

		$input = $this->addDateTimeLocal($attributes, $options, $container);
		$input->getParent()->addHidden(['name' => $name, 'value' => $value]);
		$this->addJsCode(__DIR__ . '/Form/DateTime.js');
		return $input;
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addDateTimeLocal(array $attributes = [], array $options = [], $container = null) {
		// Value must be in format "yyyy-mm-ddThh:mm"
		if (isset($attributes['value'])) {
			$date = new \Sy\Bootstrap\Lib\Date($attributes['value']);
			$attributes['value'] = $date->f("yyyy-MM-dd'T'HH:mm");
		}
		return $this->addInput('datetime-local', $attributes, $options, $container);
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addTime(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('time', $attributes, $options, $container);
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addNumber(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('number', $attributes, $options, $container);
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addRange(array $attributes = [], array $options = [], $container = null) {
		$options['label-class'] = 'form-label';
		$range = $this->addInput('range', $attributes, $options, $container);
		$range->setAttribute('class', 'form-range');
		return $range;
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return \Sy\Component\Html\Form\Checkbox
	 */
	public function addCheckbox(array $attributes = [], array $options = [], $container = null) {
		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'form-check']);
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}
		$options['label-class'] = 'form-check-label';
		$input = $div->addCheckbox($attributes, $options);
		if (!isset($attributes['id'])) {
			$input->setAttribute('id', 'checkbox_' . uniqid());
		}
		$input->addClass('form-check-input');
		return $input;
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return \Sy\Component\Html\Form\Radio
	 */
	public function addRadio(array $attributes = [], array $options = [], $container = null) {
		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'form-check']);
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}
		$options['label-class'] = 'form-check-label';
		$input = $div->addRadio($attributes, $options);
		if (!isset($attributes['id'])) {
			$input->setAttribute('id', 'checkbox_' . uniqid());
		}
		$input->addClass('form-check-input');
		return $input;
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return \Sy\Component\Html\Form\File
	 */
	public function addFile(array $attributes = [], array $options = [], $container = null) {
		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'mb-3']);
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}
		$options['error-class'] = 'invalid-feedback';
		$options['error-position'] = 'after';

		// File input
		$file = $div->addFile($attributes, $options);
		$file->addClass('form-control');

		// Size
		if (isset($options['size'])) {
			$file->addClass('form-control-' . $options['size']);
		}

		// Help text
		if (isset($options['help'])) {
			$small = new \Sy\Component\Html\Element('small');
			$small->addClass('form-text text-muted');
			$small->addText($this->_($options['help']));
			$div->addElement($small);
		}

		return $file;
	}

	/**
	 * @param  array $attributes
	 * @param  array $options label, help, addon-before, addon-after, btn-before, btn-after, error-msg-minlength, error-msg-maxlength, error-msg-pattern, floating-label
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\Textarea
	 */
	public function addTextarea(array $attributes = [], array $options = [], $container = null) {
		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'mb-3']);
		if (isset($attributes['placeholder'])) {
			$attributes['placeholder'] = $this->_($attributes['placeholder']);
		}
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}

		// Textarea
		$textarea = new Form\Textarea();
		$textarea->setAttributes($attributes);
		$textarea->addClass('form-control');

		// Floating label
		if (!empty($options['floating-label'])) {
			$div->addClass('form-floating');
			$textarea->setAttribute('placeholder', $options['label']);
			$textarea->setOption('label-position', 'after');
		}

		// Check if there is no addon
		if (!isset($options['addon-before']) and !isset($options['addon-after']) and !isset($options['btn-before']) and !isset($options['btn-after'])) {
			// Textarea
			$textarea->setOptions($options);
			$div->addElement($textarea);

			// Size
			if (isset($options['size'])) {
				$textarea->addClass('form-control-' . $options['size']);
			}
		} else { // There is addon
			// Label
			if (isset($options['label'])) {
				$label = new \Sy\Component\Html\Element('label');
				$label->addText($options['label']);
				$div->addElement($label);
				unset($options['label']);
			}

			// Input group div
			$inputGroupDiv = $div->addDiv(['class' => 'input-group']);

			// Size
			if (isset($options['size'])) {
				$inputGroupDiv->addClass('input-group-' . $options['size']);
			}

			// Input group addon before
			if (isset($options['addon-before'])) {
				$span = $inputGroupDiv->addSpan(['class' => 'input-group-text']);
				$span->addText($this->_($options['addon-before']));
			} elseif (isset($options['btn-before'])) {
				$this->addGroupButton($options['btn-before'], $inputGroupDiv);
			}

			// Textarea
			$textarea->setOptions($options);
			$inputGroupDiv->addElement($textarea);

			// Input group addon after
			if (isset($options['addon-after'])) {
				$span = $inputGroupDiv->addSpan(['class' => 'input-group-text']);
				$span->addText($this->_($options['addon-after']));
			} elseif (isset($options['btn-after'])) {
				$this->addGroupButton($options['btn-after'], $inputGroupDiv);
			}
		}

		// Help text
		if (isset($options['help'])) {
			$small = new \Sy\Component\Html\Element('small');
			$small->addClass('form-text text-muted');
			$small->addText($this->_($options['help']));
			$div->addElement($small);
		}

		// Min length validator
		if (isset($attributes['minlength'])) {
			$min = (int)$attributes['minlength'];
			$textarea->addValidator(function($value, $element) use($min, $options) {
				if (mb_strlen($value) > $min) return true;
				$element->setError($this->_(isset($options['error-msg-minlength']) ? $options['error-msg-minlength'] : ['Text min length of %d characters', $min]));
				return false;
			});
		}

		// Max length validator
		if (isset($attributes['maxlength'])) {
			$max = (int)$attributes['maxlength'];
			$textarea->addValidator(function($value, $element) use($max, $options) {
				if (mb_strlen($value) <= $max) return true;
				$element->setError($this->_(isset($options['error-msg-maxlength']) ? $options['error-msg-maxlength'] : ['Text max length of %d characters', $max]));
				return false;
			});
		}

		return $textarea;
	}

	/**
	 * @param  array $attributes
	 * @param  array $options
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\OptionContainer
	 */
	public function addSelect(array $attributes = [], array $options = [], $container = null) {
		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'mb-3']);
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}

		// Selectbox
		$select = new Form\OptionContainer('select');
		$select->setAttributes($attributes);
		$select->addClass('form-select');

		// Floating label
		if (!empty($options['floating-label'])) {
			$div->addClass('form-floating');
			$select->setOption('label-position', 'after');
		}

		// Check if there is no addon
		if (!isset($options['addon-before']) and !isset($options['addon-after']) and !isset($options['btn-before']) and !isset($options['btn-after'])) {
			// Selectbox
			$select->setOptions($options);
			$div->addElement($select);

			// Size
			if (isset($options['size'])) {
				$select->addClass('form-control-' . $options['size']);
			}
		} else { // There is addon
			// Label
			if (isset($options['label'])) {
				$label = new \Sy\Component\Html\Element('label');
				$label->addText($this->_($options['label']));
				$div->addElement($label);
				unset($options['label']);
			}

			// Input group div
			$inputGroupDiv = $div->addDiv(['class' => 'input-group']);
			if (isset($options['size'])) {
				$inputGroupDiv->addClass('input-group-' . $options['size']);
			}

			// Input group addon before
			if (isset($options['addon-before'])) {
				$span = $inputGroupDiv->addSpan(['class' => 'input-group-text']);
				$span->addText($this->_($options['addon-before']));
			} elseif (isset($options['btn-before'])) {
				$this->addGroupButton($options['btn-before'], $inputGroupDiv);
			}

			// Selectbox
			$select->setOptions($options);
			$inputGroupDiv->addElement($select);

			// Input group addon after
			if (isset($options['addon-after'])) {
				$span = $inputGroupDiv->addSpan(['class' => 'input-group-text']);
				$span->addText($this->_($options['addon-after']));
			} elseif (isset($options['btn-after'])) {
				$this->addGroupButton($options['btn-after'], $inputGroupDiv);
			}
		}

		// Help text
		if (isset($options['help'])) {
			$small = new \Sy\Component\Html\Element('small');
			$small->addClass('form-text text-muted');
			$small->addText($this->_($options['help']));
			$div->addElement($small);
		}
		return $select;
	}

	/**
	 * Add button
	 *
	 * @param  string $label
	 * @param  array $attributes
	 * @param  array $options label, icon, color, size
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return \Sy\Component\Html\Form\Element
	 */
	public function addButton($label, array $attributes = [], array $options = [], $container = null) {
		$color = isset($options['color']) ? $options['color'] : 'secondary';
		$attributes['class'] = (isset($attributes['class']) ? $attributes['class'] . ' ' : '') . "btn btn-$color" . (empty($options['size']) ? '' : " btn-${options['size']}");

		// Icon
		if (isset($options['icon'])) {
			$iconAttributes = is_string($options['icon']) ? ['class' => $options['icon']] : $options['icon'];
			$span = new \Sy\Component\Html\Element('span');
			$span->setAttributes($iconAttributes);
			$text = $span->__toString() . ' ' . $this->_($label);
		} else {
			$text = $this->_($label);
		}

		if (is_null($container)) {
			return parent::addButton($text, $attributes);
		} else {
			return $container->addButton($text, $attributes);
		}
	}

	/**
	 * Add input
	 *
	 * @param  string $class
	 * @param  array $attributes
	 * @param  array $options label, help, addon-before, addon-after, btn-before, btn-after, error-msg-minlength, error-msg-maxlength, error-msg-pattern, floating-label
	 * @param  \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	protected function addInput($class, array $attributes = [], array $options = [], $container = null) {
		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'mb-3']);
		if (isset($attributes['placeholder'])) {
			$attributes['placeholder'] = $this->_($attributes['placeholder']);
		}
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}
		if (isset($attributes['pattern'])) {
			$attributes['pattern'] = str_replace('{', '&#123;', $attributes['pattern']);
		}

		// Input
		$input = new Form\TextFillableInput($class);
		$input->setAttributes($attributes);
		$input->addClass('form-control');

		// Floating label
		if (!empty($options['floating-label'])) {
			$div->addClass('form-floating');
			$input->setAttribute('placeholder', $options['label']);
			$input->setOption('label-position', 'after');
		}

		// Check if there is no addon
		if (!isset($options['addon-before']) and !isset($options['addon-after']) and !isset($options['btn-before']) and !isset($options['btn-after'])) {
			$input->setOptions($options);
			$div->addElement($input);
			if (isset($options['size'])) {
				$input->addClass('form-control-' . $options['size']);
			}
		} else { // There is addon
			// Label
			if (isset($options['label'])) {
				$label = new \Sy\Component\Html\Element('label');
				$label->addText($options['label']);
				$div->addElement($label);
				unset($options['label']);
			}

			// Input group div
			$inputGroupDiv = $div->addDiv(['class' => 'input-group']);
			if (isset($options['size'])) {
				$inputGroupDiv->addClass('input-group-' . $options['size']);
			}

			// Input group addon before
			if (isset($options['addon-before'])) {
				$span = $inputGroupDiv->addSpan(['class' => 'input-group-text']);
				$span->addText($this->_($options['addon-before']));
			} elseif (isset($options['btn-before'])) {
				$this->addGroupButton($options['btn-before'], $inputGroupDiv);
			}

			// Input
			$input->setOptions($options);
			$inputGroupDiv->addElement($input);

			// Input group addon after
			if (isset($options['addon-after'])) {
				$span = $inputGroupDiv->addSpan(['class' => 'input-group-text']);
				$span->addText($this->_($options['addon-after']));
			} elseif (isset($options['btn-after'])) {
				$this->addGroupButton($options['btn-after'], $inputGroupDiv);
			}
		}

		// Help text
		if (isset($options['help'])) {
			$small = new \Sy\Component\Html\Element('small');
			$small->addClass('form-text text-muted');
			$small->addText($this->_($options['help']));
			$div->addElement($small);
		}

		// Min length validator
		if (isset($attributes['minlength'])) {
			$min = (int)$attributes['minlength'];
			$error = isset($options['error-msg-minlength']) ? $options['error-msg-minlength'] : ['Text min length of %d characters', $min];
			$input->addValidator(function($value, $element) use($min, $error) {
				if (mb_strlen($value) > $min) return true;
				$element->setError($this->_($error));
				return false;
			});
		}

		// Max length validator
		if (isset($attributes['maxlength'])) {
			$max = (int)$attributes['maxlength'];
			$error = isset($options['error-msg-maxlength']) ? $options['error-msg-maxlength'] : ['Text max length of %d characters', $max];
			$input->addValidator(function($value, $element) use($max, $error) {
				if (mb_strlen($value) <= $max) return true;
				$element->setError($this->_($error));
				return false;
			});
		}

		// Pattern validator
		if (isset($attributes['pattern'])) {
			$pattern = '/^(?:' . $attributes['pattern'] . ')$/';
			$error = isset($options['error-msg-pattern']) ? $options['error-msg-pattern'] : 'Pattern error';
			$input->addValidator(function($value, $element) use($pattern, $error) {
				if (preg_match($pattern, $value) === 1) return true;
				$element->setError($this->_($error));
				return false;
			});
		}

		return $input;
	}

	protected function addGroupButton($button, $container) {
		$label      = is_string($button) ? $button : '';
		$attributes = [];
		$options    = [];
		if (is_array($button)) {
			$label      = empty($button['label']) ? '' : $button['label'];
			$attributes = empty($button['attributes']) ? [] : $button['attributes'];
			$options    = empty($button['options']) ? [] : $button['options'];
		}
		$this->addButton($label, $attributes, $options, $container);
	}

	private function trim($v) {
		if (!is_array($v)) return trim($v);
		return array_map([$this, 'trim'], $v);
	}

}