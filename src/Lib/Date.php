<?php
namespace Sy\Bootstrap\Lib;

class Date {

	/**
	 * @var DateTime
	 */
	private $dateTime;

	/**
	 * @var \Sy\Translate\ITranslator
	 */
	private $translator;

	public function __construct($time) {
		$this->dateTime = new \DateTime($time);
		$this->translator = \Sy\Translate\TranslatorProvider::createTranslator(__DIR__ . '/Date');
	}

	public function humanTimeDiff() {
		$now = new \DateTime();
		$interval = $now->diff($this->dateTime);
		$s = $interval->invert == 0 ? 'in %s' : '%s ago';
		if ($interval->y == 1 and $interval->m < 2)  return $interval->format($this->translator->translate(sprintf($s, 'a year')));
		if ($interval->y == 1 and $interval->m < 12) return $interval->format($this->translator->translate(sprintf($s, 'a year and %m months')));
		if ($interval->y > 1)                        return $interval->format($this->translator->translate(sprintf($s, '%y years')));
		if ($interval->m == 1 and $interval->d < 2)  return $interval->format($this->translator->translate(sprintf($s, 'a month')));
		if ($interval->m == 1 and $interval->d < 30) return $interval->format($this->translator->translate(sprintf($s, 'a month and %d days')));
		if ($interval->m > 1)                        return $interval->format($this->translator->translate(sprintf($s, '%m months')));
		if ($interval->d == 1 and $interval->h < 2)  return $interval->format($this->translator->translate($interval->invert == 0 ? 'tomorrow' : 'yesterday'));
		if ($interval->d == 1 and $interval->h < 24) return $interval->format($this->translator->translate(sprintf($s, 'a day and %h hours')));
		if ($interval->d > 1)                        return $interval->format($this->translator->translate(sprintf($s, '%d days')));
		if ($interval->h == 1 and $interval->i < 2)  return $interval->format($this->translator->translate(sprintf($s, 'an hour')));
		if ($interval->h == 1 and $interval->i < 60) return $interval->format($this->translator->translate(sprintf($s, 'an hour and %i minutes')));
		if ($interval->h > 1)                        return $interval->format($this->translator->translate(sprintf($s, '%h hours')));
		if ($interval->i == 1)                       return $interval->format($this->translator->translate(sprintf($s, 'a minute')));
		if ($interval->i > 1)                        return $interval->format($this->translator->translate(sprintf($s, '%i minutes')));
		return $interval->format($this->translator->translate('just now'));
	}

	public function date() {
		return $this->f($this->translator->translate('Y-m-d'));
	}

	public function f($format) {
		return $this->dateTime->format($format);
	}

	public function timestamp() {
		return $this->dateTime->getTimestamp();
	}

}