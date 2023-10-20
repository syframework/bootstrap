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

	/**
	 * @param string $time A date/time string. Valid formats are explained in https://www.php.net/manual/en/datetime.formats.php
	 */
	public function __construct($time = 'now') {
		$this->dateTime = new \DateTime($time);
		$this->translator = \Sy\Translate\TranslatorProvider::createTranslator(__DIR__ . '/Date');
	}

	/**
	 * Returns a string representation of a this time relative to now, such as "2 days ago" or "in 2 days".
	 *
	 * @return string
	 */
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

	/**
	 * Returns the date.
	 *
	 * @return string
	 */
	public function date() {
		return $this->f($this->translator->translate('yyyy-MM-dd'));
	}

	/**
	 * Format the date/time value as a string.
	 *
	 * @param  string $format Possible patterns are documented at https://unicode-org.github.io/icu/userguide/format_parse/datetime
	 * @param  string $timezone List of supported timezones: https://www.php.net/manual/en/timezones.php
	 * @return string
	 */
	public function f($format, $timezone = null) {
		$service = \Project\Service\Container::getInstance();
		$lang = $service->lang->getLang();
		$formatter = new \IntlDateFormatter($lang, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $timezone, null, $format);
		return $formatter->format($this->timestamp());
	}

	/**
	 * Returns the Unix timestamp representing the date.
	 *
	 * @return int
	 */
	public function timestamp() {
		return $this->dateTime->getTimestamp();
	}

}