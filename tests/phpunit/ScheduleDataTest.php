<?php

namespace AKlump\LoftDataGrids;


class ScheduleDataTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadWeekdayFormatThrows()
    {
        $this->obj->addWeekdayOff('fish');
    }
    
    /**
     * Provides data for testMakeSchedule.
     */
    function DataForTestMakeScheduleProvider()
    {
        $tests = array();
        $tests[] = array(
            '{"03\/27\/2017":[{"task":"do","time":8}],"03\/28\/2017":[{"task":"do","time":8}],"03\/29\/2017":[{"task":"do","time":4},{"task":"re","time":2},{"task":"mi","time":2}],"03\/30\/2017":[{"task":"mi","time":3}],"Schedule Statistics":[{"start date":"03\/27\/2017","end date":"03\/30\/2017","total hours":27,"total items":3,"hours per day":8,"days off":0,"weekdays off":"Sat, Sun","dates off":""}]}',
            '183c4ed456d5aa89641bbbf420174e0ca4332c6f',
            8,
            array(
                array('do', 20),
                array('re', 2),
                array('mi', 5),
            ),
            // no weekends
            array('sat', 'sun'),
            // then entire first week off.
            array('2017-03-20','2017-03-21','2017-03-22','2017-03-23','2017-03-24'),
            true,
        );
        $tests[] = array(
            '{"03\/20\/2017":[{"task":"do","time":8}],"03\/22\/2017":[{"task":"re","time":8}],"Schedule Statistics":[{"start date":"03\/20\/2017","end date":"03\/22\/2017","total hours":16,"total items":2,"hours per day":8,"days off":1,"weekdays off":"Sun, Tue","dates off":"03\/21\/2017"}]}',
            'a3cc0f58b15a3e9708387441e3955e055dd24191',
            8,
            array(
                array('do', 8),
                array('re', 8),
            ),
            array('sun', 'tue'),
            array(),
            true,
        );

        $tests[] = array(
            '{"03\/19\/2017":[{"task":"do","time":3},{"task":"re","time":5}],"03\/20\/2017":[{"task":"mi","time":8}]}',
            '2390a77da7249aaf526bbe67880722a6ffd18c74',
            8,
            array(
                array('do', 3),
                array('re', 5),
                array('mi', 8),
            ),
        );
        $tests[] = array(
            '{"03\/19\/2017":[{"task":"do","time":8}],"03\/20\/2017":[{"task":"do","time":8}],"03\/21\/2017":[{"task":"do","time":4},{"task":"re","time":2},{"task":"mi","time":2}],"03\/22\/2017":[{"task":"mi","time":3}]}',
            '42e040740cc27c45b57bd7523027108dc8eb6a45',
            8,
            array(
                array('do', 20),
                array('re', 2),
                array('mi', 5),
            ),
        );
        $tests[] = array(
            '{"03\/19\/2017":[{"task":"do","time":8}],"03\/20\/2017":[{"task":"re","time":8}]}',
            '0dde9e3b0c722326b1b3402665b1fc35d51a3fa6',
            8,
            array(
                array('do', 8),
                array('re', 8),
            ),
        );
        $tests[] = array(
            '{"03\/19\/2017":[{"task":"do","time":8}],"03\/20\/2017":[{"task":"do","time":8}],"03\/21\/2017":[{"task":"do","time":4}]}',
            'e18fb2fb1d3442812256e4cb53fc34f8471b3a38',
            8,
            array(
                array('do', 20),
            ),
        );

        return $tests;
    }

    /**
     * @dataProvider DataForTestMakeScheduleProvider
     */
    public function testMakeSchedule($json, $hash, $hoursPerDay, $todos, $weekdays = array(), $holidays = array(), $stats = false)
    {
        $data = $this->obj;
        $data->setHoursPerDay($hoursPerDay)
             ->setHoursKey('time')
             ->setStartDate(new \DateTime('2017-03-19', new \DateTimeZone('America/Los_Angeles')));

        foreach ($weekdays as $weekday) {
            $data->addWeekdayOff($weekday);
        }

        foreach ($holidays as $holiday) {
            $data->addDateOff(new \DateTime($holiday, new \DateTimeZone('America/Los_Angeles')));
        }

        // Add all tasks
        foreach ($todos as $todo) {
            $data->add('task', $todo[0])
                 ->add('time', $todo[1])
                 ->next();
        }

        // Now schedule
        $data->makeSchedule();
        if ($stats) {
            $data->addStatsPage();
        }

        $out = new JSONExporter($data);
        $this->assertSame($json, $out->export());

        $this->assertSame($hash, strval($data));

    }

    public function testSetDatePage()
    {
        $data = $this
            ->obj->setDatePage(new \DateTime('2017-03-19', new \DateTimeZone('America/Los_Angeles')))
                 ->add('do', 're');
        $control = 'ec893faef9fcb6f71c33a728bb83becbfe4d31c0';
        $this->assertSame($control, strval($data));

        $out = new JSONExporter($data);
        $this->assertSame('{"03\/19\/2017":[{"do":"re"}]}', $out->export());
    }

    public function testSetGetStartDate()
    {
        $control = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
        $this->obj->setStartDate($control);
        $this->assertSame($control, $this->obj->getStartDate());
    }

    public function testPageIdFormat()
    {
        $control = 'mdY';
        $this->obj->setPageIdFormat($control);
        $this->assertSame($control, $this->obj->getPageIdFormat());
    }

    public function testConfiguration()
    {
        $this->obj->setHoursPerDay(8);
        $this->obj->setHoursKey('time');
        $this->assertSame(8, $this->obj->getHoursPerDay());
        $this->assertSame('time', $this->obj->getHoursKey());
    }

    public function setUp()
    {
        $this->obj = new ScheduleData();
    }
}
