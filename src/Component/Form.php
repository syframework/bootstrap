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
		$f->addClass('d-none');
		$f->addTextInput(
			['name' => 'sy_firstname'],
			['validator' => function($p) {return is_null($p);}]
		);
		$f->addTextInput(
			['name' => 'sy_lastname', 'value' => 'Your lastname'],
			['validator' => function($p) {return $p === 'Your lastname';}]
		);
		return $this->addElement($f);
	}

	public function addCsrfField() {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
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
				}
			]
		);
	}

	/**
	 * Add a text input with some bootstrap options
	 *
	 * @param array $attributes
	 * @param array $options label, help, size (sm or lg), addon-before, addon-after
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return \Sy\Component\Html\Form\TextFillableInput
	 */
	public function addTextInput(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('text', $attributes, $options, $container);
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addPassword(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('password', $attributes, $options, $container);
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
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
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addTel(array $attributes = [], array $options = [], $container = null) {
		// Add Intl tel input JS and CSS
		$this->addCssLink(INTLTELINPUT_CSS);
		$this->addJsLink(INTLTELINPUT_JS);
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/Form/Tel.js');
		$js->setVars([
			'INTLTELINPUT_UTILS_JS' => INTLTELINPUT_UTILS_JS,
			'WEB_ROOT' => WEB_ROOT
		]);
		$this->addJsCode($js->__toString());

		$attributes['data-name'] = $attributes['name'];
		$attributes['data-error'] = $this->_('Invalid phone number');
		$attributes['data-help'] = isset($options['help']) ? $this->_($options['help']) : '';
		$options['help'] = isset($options['help']) ? $options['help'] : '';

		// Add a hidden input
		parent::addTextInput(['name' => $attributes['name'], 'class' => 'tel-hidden-input d-none']);
		$attributes['name'] = $attributes['name'] . '-raw';

		return $this->addInput('tel', $attributes, $options, $container);
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addUrl(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('url', $attributes, $options, $container);
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addDate(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('date', $attributes, $options, $container);
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addDateTime(array $attributes = [], array $options = [], $container = null) {
		return $this->addDateTimeLocal($attributes, $options, $container);
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addDateTimeLocal(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('datetime-local', $attributes, $options, $container);
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addTime(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('time', $attributes, $options, $container);
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return Form\TextFillableInput
	 */
	public function addNumber(array $attributes = [], array $options = [], $container = null) {
		return $this->addInput('number', $attributes, $options, $container);
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return \Sy\Component\Html\Form\Checkbox
	 */
	public function addCheckbox(array $attributes = [], array $options = [], $container = null) {
		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'custom-control custom-checkbox']);
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}
		$options['label-class'] = 'custom-control-label';
		$input = $div->addCheckbox($attributes, $options);
		if (!isset($attributes['id'])) {
			$input->setAttribute('id', 'checkbox_' . uniqid());
		}
		$input->addClass('custom-control-input');
		return $input;
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return \Sy\Component\Html\Form\Textarea
	 */
	public function addTextarea(array $attributes = [], array $options = [], $container = null) {
		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'form-group']);
		if (isset($attributes['placeholder'])) {
			$attributes['placeholder'] = $this->_($attributes['placeholder']);
		}
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}
		$options['error-class'] = 'invalid-feedback';
		$options['error-position'] = 'after';

		// Check if there is no addon
		if (!isset($options['addon-before']) and !isset($options['addon-after']) and !isset($options['btn-before']) and !isset($options['btn-after'])) {
			// Textarea
			$textarea = $div->addTextarea($attributes, $options);
			$textarea->addClass('form-control');

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
				$span = $inputGroupDiv->addDiv(['class' => 'input-group-prepend'])->addSpan(['class' => 'input-group-text']);
				$span->addText($options['addon-before']);
			} elseif (isset($options['btn-before'])) {
				$this->addGroupButton($options['btn-before'], $inputGroupDiv->addDiv(['class' => 'input-group-prepend']));
			}

			// Textarea
			$textarea = $inputGroupDiv->addTextarea($attributes, $options);
			$textarea->addClass('form-control');

			// Input group addon after
			if (isset($options['addon-after'])) {
				$span = $inputGroupDiv->addDiv(['class' => 'input-group-append'])->addSpan(['class' => 'input-group-text']);
				$span->addText($options['addon-after']);
			} elseif (isset($options['btn-after'])) {
				$this->addGroupButton($options['btn-after'], $inputGroupDiv->addDiv(['class' => 'input-group-append']));
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
			$min = (int) $attributes['minlength'];
			$textarea->addValidator(function($value) use($min, $textarea) {
				$l = strlen($value);
				if ($l > $min) return true;
				$this->setError(sprintf($this->_("Text min length of %d characters"), $min));
				$textarea->addClass('is-invalid');
				return false;
			});
		}

		// Max length validator
		if (isset($attributes['maxlength'])) {
			$max = (int) $attributes['maxlength'];
			$textarea->addValidator(function($value) use($max, $textarea) {
				$l = strlen($value);
				if ($l <= $max) return true;
				$this->setError(sprintf($this->_("Text max length of %d characters"), $max));
				$textarea->addClass('is-invalid');
				return false;
			});
		}

		return $textarea;
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @param \Sy\Component\Html\Form\FieldContainer $container
	 * @return \Sy\Component\Html\Form\OptionContainer
	 */
	public function addSelect(array $attributes = [], array $options = [], $container = null) {
		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'form-group']);
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}

		// Check if there is no addon
		if (!isset($options['addon-before']) and !isset($options['addon-after']) and !isset($options['btn-before']) and !isset($options['btn-after'])) {
			// Selectbox
			$select = $div->addSelect($attributes, $options);
			$select->addClass('custom-select');

			// Size
			if (isset($options['size'])) {
				$select->addClass('form-control-' . $options['size']);
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
				$span = $inputGroupDiv->addDiv(['class' => 'input-group-prepend'])->addSpan(['class' => 'input-group-text']);
				$span->addText($options['addon-before']);
			} elseif (isset($options['btn-before'])) {
				$this->addGroupButton($options['btn-before'], $inputGroupDiv->addDiv(['class' => 'input-group-prepend']));
			}

			// Selectbox
			$select = $inputGroupDiv->addSelect($attributes, $options);
			$select->addClass('custom-select');

			// Input group addon after
			if (isset($options['addon-after'])) {
				$span = $inputGroupDiv->addDiv(['class' => 'input-group-append'])->addSpan(['class' => 'input-group-text']);
				$span->addText($options['addon-after']);
			} elseif (isset($options['btn-after'])) {
				$this->addGroupButton($options['btn-after'], $inputGroupDiv->addDiv(['class' => 'input-group-append']));
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
	 * @param string $label
	 * @param array $attributes
	 * @param array $options label, icon, color, size
	 * @param \Sy\Component\Html\Form\FieldContainer $container
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

	protected function addInput($class, array $attributes = [], array $options = [], $container = null) {
		$element = new Form\TextFillableInput($class);

		if (is_null($container)) $container = $this;
		$div = $container->addDiv(['class' => 'form-group']);
		if (isset($attributes['placeholder'])) {
			$attributes['placeholder'] = $this->_($attributes['placeholder']);
		}
		if (isset($options['label'])) {
			$options['label'] = $this->_($options['label']);
		}
		$options['error-class'] = 'invalid-feedback';
		$options['error-position'] = 'after';

		// Check if there is no addon
		if (!isset($options['addon-before']) and !isset($options['addon-after']) and !isset($options['btn-before']) and !isset($options['btn-after'])) {
			$element->setAttributes($attributes);
			$element->setOptions($options);
			$textInput = $div->addElement($element);
			$textInput->addClass('form-control');
			if (isset($options['size'])) {
				$textInput->addClass('form-control-' . $options['size']);
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
				$span = $inputGroupDiv->addDiv(['class' => 'input-group-prepend'])->addSpan(['class' => 'input-group-text']);
				$span->addText($options['addon-before']);
			} elseif (isset($options['btn-before'])) {
				$this->addGroupButton($options['btn-before'], $inputGroupDiv->addDiv(['class' => 'input-group-prepend']));
			}

			// Input
			$element->setAttributes($attributes);
			$element->setOptions($options);
			$textInput = $inputGroupDiv->addElement($element);
			$textInput->addClass('form-control');

			// Input group addon after
			if (isset($options['addon-after'])) {
				$span = $inputGroupDiv->addDiv(['class' => 'input-group-append'])->addSpan(['class' => 'input-group-text']);
				$span->addText($options['addon-after']);
			} elseif (isset($options['btn-after'])) {
				$this->addGroupButton($options['btn-after'], $inputGroupDiv->addDiv(['class' => 'input-group-append']));
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
			$min = (int) $attributes['minlength'];
			$textInput->addValidator(function($value) use($min, $textInput) {
				$l = strlen($value);
				if ($l > $min) return true;
				$this->setError(sprintf($this->_("Text min length of %d characters"), $min));
				$textInput->addClass('is-invalid');
				return false;
			});
		}

		// Max length validator
		if (isset($attributes['maxlength'])) {
			$max = (int) $attributes['maxlength'];
			$textInput->addValidator(function($value) use($max, $textInput) {
				$l = strlen($value);
				if ($l <= $max) return true;
				$this->setError(sprintf($this->_("Text max length of %d characters"), $max));
				$textInput->addClass('is-invalid');
				return false;
			});
		}

		return $textInput;
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

namespace Sy\Bootstrap\Component\Form;

class CsrfException extends \Sy\Component\Html\Form\Exception {}