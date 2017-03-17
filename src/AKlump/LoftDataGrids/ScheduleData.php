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

        $singleDayHours = 0;
        $date = clone $this->getStartDate();
        $this->setDatePage($date);
        foreach ($items as $item) {
            foreach ($item as $key => $value) {
                $this->add($key, $value);
            }
            $this->next();

            // Test if we've gone over the day.
            $hours = $item[$this->getHoursColumn()];
            $singleDayHours += $hours;
            if (($over = $singleDayHours - $this->getHoursPerDay()) > 0) {
                //
                //
                // Go to the next day.
                //
                $date->add(new \DateInterval('P1D'));

                // Advance past dates/weekdays not worked.
                while ($this->datesOff && $this->weekdaysOff && in_array($date, $this->datesOff) || in_array($date->format('D'), $this->weekdaysOff)) {
                    $date->add(new \DateInterval('P1D'));
                }

                $singleDayHours = $over;
                $this->setDatePage($date);
            }
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
        $this->startDate = $startDate;

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
    public function getHoursColumn()
    {
        return $this->hoursKey;
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
     * @param mixed $hoursKey
     *
     * @return ScheduleData
     */
    public function setHoursKey($hoursKey)
    {
        $this->hoursKey = $hoursKey;

        return $this;
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
        $this->datesOff[] = $holiday;

        return $this;
    }
}
