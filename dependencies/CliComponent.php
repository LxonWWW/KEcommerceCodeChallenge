<?php
/*
    Author: kontakt@leondierkes.de
    Description: Handles CLI functions
*/

class CliComponent {
    /*
    $text - The text to output in the console
    $color - An ANSI formatted color code, see https://misc.flogisoft.com/bash/tip_colors_and_formatting for reference
    $type - Defines the type of the message. When set to error it will be written to the log and the program will quit.
    $issuer - Defines the issuer of the message (the text in the square brackets at the beginning of each message)
    */
    public function println(string $text, string $color="\e[0m", string $type="message", string $issuer="System") {
        echo $color."[".$issuer."] ".$text."\e[0m\n";

        if($type === "warning") {
            $this->logError($text);
        }

        if($type === "error") {
            $this->logError($text);
            die();
        }
    }

    public function getColor(string $color) {
        switch($color) {
            case "black":
                return "\e[30m";
                break;
            case "red":
                return "\e[31m";
                break;
            case "green":
                return "\e[32m";
                break;
            case "yellow":
                return "\e[33m";
                break;
            case "blue":
                return "\e[34m";
                break;
            case "magenta":
                return "\e[35m";
                break;
            case "cyan":
                return "\e[36m";
                break;
            case "light_gray":
                return "\e[37m";
                break;
            case "dark_gray":
                return "\e[90m";
                break;
            case "light_red":
                return "\e[91m";
                break;
            case "light_green":
                return "\e[92m";
                break;
            case "light_yellow":
                return "\e[93m";
                break;
            case "light_blue":
                return "\e[94m";
                break;
            case "light_magenta":
                return "\e[95m";
                break;
            case "light_cyan":
                return "\e[96m";
                break;
            default:
                return "\e[39m";
                break;
        }
    }

    public function getFilePath($argc, $argv) {
        try {
            if(!($argc >= 2)) {
                throw new Exception("Too few or too many arguments passed, but only 1 argument is needed (max. 2 allowed) (eg. 'php ./run.php ./feed.xml (optional=updateallowed)').");
            }

            if(!isset($argv[1]))
                throw new Exception("No parameter provided for filepath. (eg. 'php ./run.php ./feed.xml').");
    
            if(!file_exists($argv[1]))
                throw new Exception("File not found: ".$argv[1].".");

            return $argv[1];
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function getUpdateAllowed($argc, $argv) {
        try {
            if($argc !== 3) {
                return false;
            }

            if(!isset($argv[2]))
                throw new Exception("No parameter provided for updateAllowed. (eg. 'php ./run.php ./feed.xml').");
    
            if($argv[2] !== "updateallowed")
                throw new Exception("Unknown argument: ".$argv[2].".");

            return true;
        } catch(Exception $e) {
            $this->println($e->getMessage(), $this->getColor("red"), "error");
        }

        return false;
    }

    private function logError($message) {
        file_put_contents(pathinfo(__FILE__)['dirname']."/../error.log", "[".date("m.d.Y h:i:s")."] ".$message."\n", FILE_APPEND);
    }
}
?>