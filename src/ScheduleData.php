<?php

namespace AKlump\LoftDataGrids;

/**
 * Class ScheduleData
 *
 * Use this object to create schedules from a data set where each record
 * contains an hours value.  First add all of your tasks as a single page.
 * Then call makeSchedule() after setting necessary parameters like weekdays
 * off, or black out dates.
 *
 * @code
 * $tz = new \DateTimeZone('America/Los_Angeles');
 * $schedule = new ScheduleData();
 * $schedule->setHoursKey('hours')
 *          ->setHoursPerDay(6)
 *          ->addWeekdayOff('Sat')
 *          ->addWeekdayOff('Sun')
 *          ->addDateOff(date_create('1/2/17', $tz)
 *          ->setStartDate(date_create('tomorrow', $tz));
 * // Now add some tasks.
 * $schedule
 *     ->add('task', 'Clean house')
 *     ->add('hours', 2)
 *     ->next();
 * $schedule
 *     ->add('task', 'Wash car')
 *     ->add('hours', 1)
 *     ->next()
 * $schedule->makeSchedule();
 * // Proceed to an export formatter now.
 * @endcode
 *
 * @package AKlump\Progress
 */
class ScheduleData extends ExportData {

    protected $startDate;
    protected $datesOff = array();

    /**
     * @var string The key where the hours can be found
     */
    protected $hoursKey = 'hours';

    /**
     * @var array Weekdays which should not be scheduled.
     */
    protected $weekdaysOff = array();

    /**
     * @var int How many hours to schedule each day?
     */
    protected $hoursPerDay = 8;

    /**
     * @var string The format used for naming pages by date.
     */
    protected $pageIdFormat = 'm/d/Y';

    protected $stats = array();
    protected $todoStatsPage = false;

    /**
     * Split the current page up into a number of pages based on workload.
     *
     * @return $this
     */
    public function makeSchedule()
    {
        // Pull out the first page to distribute it; then delete.
        $items = $this->getRows();
        $id = $this->getCurrentPageId();
        $this->deletePage($id);

        $this->date = clone $this->getStartDate();
        $this->avoidHolidaysAndWeekdaysOff();
        $this->stats['start date'] = $this->date->format($this->getPageIdFormat());
        $this->stats['hours per day'] = $this->getHoursPerDay();
        $this->stats['total items'] = count($items);
        $this->stats['dates off'] = array();
        $this->setDatePage($this->date);

        $dayTotal = 0;
        $this->stats['total hours'] = 0;
        while (($item = array_shift($items))) {
            $this->stats['total hours'] += $item[$this->getHoursKey()];
            $this->processItem($item, $item[$this->getHoursKey()], $dayTotal);
        }
        $this->stats['end date'] = $this->date->format($this->getPageIdFormat());

        if ($this->todoStatsPage) {
            $this->addStatsPage($this->todoStatsPage);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return ($d = $this->startDate) ? $d : date_create('today');
    }

    /**
     * @param mixed $startDate
     *
     * @return ScheduleData
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate
            ->setTimezone(new \DateTimeZone('UTC'))
            ->setTime(0, 0, 0);

        return $this;
    }

    /**
     * Increment the internal date until it falls on neither a holiday nor a
     * weekday that can't be worked.
     */
    protected function avoidHolidaysAndWeekdaysOff()
    {
        while (in_array($this->date, $this->datesOff)
            || in_array($this->date->format('D'), $this->weekdaysOff)) {
            $this->stats['dates off'][] = $this->date->format($this->getPageIdFormat());
            $this->date->add(new \DateInterval('P1D'));
        }
    }

    /**
     * @return string
     */
    public function getPageIdFormat()
    {
        return $this->pageIdFormat;
    }

    /**
     * @param string $pageIdFormat
     *
     * @return ScheduleData
     */
    public function setPageIdFormat($pageIdFormat)
    {
        $this->pageIdFormat = $pageIdFormat;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHoursPerDay()
    {
        return $this->hoursPerDay;
    }

    /**
     * @param mixed $hoursPerDay
     *
     * @return ScheduleData
     */
    public function setHoursPerDay($hoursPerDay)
    {
        $this->hoursPerDay = intval($hoursPerDay);

        return $this;
    }

    /**
     * Set a page based on a datetime object.
     *
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDatePage(\DateTime $date)
    {
        return $this->setPage($date->format($this->getPageIdFormat()));
    }

    /**
     * @return mixed
     */
    public function getHoursKey()
    {
        return $this->hoursKey;
    }

    /**
     * @param mixed $hoursKey
     *
     * @return ScheduleData
     */
    public function setHoursKey($hoursKey)
    {
        $this->hoursKey = $hoursKey;

        return $this;
    }

    protected function processItem($item, $hours, &$dayTotal)
    {
        $available = $this->getHoursPerDay() - $dayTotal;
        if ($available === 0) {
            $this->nextDay();
            $dayTotal = 0;

            return $this->processItem($item, $hours, $dayTotal);
        }
        elseif ($hours > $available) {
            $this->addTodoItem($item, $available);
            $this->nextDay();
            $dayTotal = 0;
            $remain = $hours - $available;

            return $this->processItem($item, $remain, $dayTotal);
        }

        $dayTotal += $hours;

        return $this->addTodoItem($item, $hours);
    }

    public function addStatsPage($title = 'Schedule Statistics')
    {
        if (empty($this->stats)) {
            $id = $this->getCurrentPageId();
            $this->setPage($title);
            // Add this to ensure placement in the page orders FIFO
            $this->add('start date', null);
            $this->todoStatsPage = $title;
            $this->setPage($id);
        }
        else {
            $this->setPage($title);
            $this->add('days off', count($this->stats['dates off']));
            $this->add('weekdays off', implode(', ', $this->weekdaysOff));
            $this->stats['dates off'] = implode(', ', $this->stats['dates off']);
            foreach ($this->stats as $key => $value) {
                $this->add($key, $value);
            }
            $this->next();
            $this->setKeys('start date', 'end date', 'total hours', 'total items', 'hours per day', 'days off', 'weekdays off', 'dates off');
        }

        return $this;
    }

    /**
     * Advance to the next available work day.
     */
    protected function nextDay()
    {
        $this->date->add(new \DateInterval('P1D'));
        $this->avoidHolidaysAndWeekdaysOff();
        $this->setDatePage($this->date);
    }

    protected function addTodoItem($item, $hours)
    {
        foreach ($item as $key => $value) {
            switch ($key) {
                case $this->getHoursKey():
                    $this->add($key, $hours);
                    break;
                default:
                    $this->add($key, $value);

                    break;
            }
        }
        $this->next();
    }

    /**
     * Mark a single weekday as unworked.
     *
     * @param string $value Three char word ,e.g. 'Sun', 'Mon'...
     *
     * @return $this
     */
    public function addWeekdayOff($value)
    {
        $value = ucfirst(strtolower($value));
        if (!(date_create_from_format('D', $value))) {
            throw new \InvalidArgumentException("Bad format of day of week.");
        }
        $this->weekdaysOff[] = $value;

        return $this;
    }

    /**
     * Add a date of no work (holiday, vacation, sick, etc).
     *
     * @param \DateTime $holiday
     *
     * @return $this
     */
    public function addDateOff(\DateTime $holiday)
    {
        $this->datesOff[] = $holiday
            ->setTimezone(new \DateTimeZone('UTC'))
            ->setTime(0, 0, 0);

        return $this;
    }
}
