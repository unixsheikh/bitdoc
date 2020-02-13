<?php
/**
 * Print color messages out to the console.
 *
 * @package Bitdoc
 */
namespace Bitdoc;

class ConsolePrint
{
    /**
     * Print with newline at the top.
     *
     * @param  string $string The message.
     * @param  string $status The message status.
     * @param  string $color The color of the message.
     *
     * @return void
     */
    public function printtnl($string, $status, $color = null)
    {
        echo "\n";
        $this->print($string, $status, $color = null);
    }

    /**
     * Print status messages on the console.
     *
     * @param  string $string The message.
     * @param  string $status The message status.
     * @param  string $color The color of the message.
     *
     * @return void
     */
    public function print($string, $status, $color = null)
    {
        $printMask = "%-54s [ %s ]\n";

        switch ($color) {
            case 'red':
                printf($printMask, $string, "\033[1;31m$status\033[0m");
                break;
            case 'yellow':
                printf($printMask, $string, "\033[1;33m$status\033[0m");
                break;
            default:
                printf($printMask, $string, "\033[1;32m$status\033[0m");
                break;
        }
    }
}
