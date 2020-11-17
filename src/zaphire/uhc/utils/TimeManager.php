<?php

namespace zaphire\uhc\utils;

use zaphire\uhc\ZaphireUHC;

class TimeManager
{
    /** @var int */
    private $nextGameTime = 0;
    /** @var ZaphireUHC */
    private $plugin;

    /**
     * TimeManager constructor.
     * @param ZaphireUHC $plugin
     */
    public function __construct(ZaphireUHC $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return int
     */
    public function getNextGameTime(): int
    {
        return $this->nextGameTime;
    }

    /**
     * @param string $nextGameTime
     */
    public function setNextGameTime(string $nextGameTime): void
    {
        $this->nextGameTime = strtotime($nextGameTime) ?: strtotime("1 hour");
    }

    public function canJoinEvent(): bool
    {
        return $this->nextGameTime < time() || $this->nextGameTime === 0;
    }


}