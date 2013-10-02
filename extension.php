<?php
// Bot Killer Extension for Bolt, by Prasetyo Wicaksono

namespace BotKiller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Extension extends \Bolt\BaseExtension
{

    /**
     * Info block for Bot Killer Extension.
     */
    function info()
    {

        $data = array(
            'name' => "Bot Killer",
            'description' => "Honeypot http:BL implementation to kill spammer and naugthy bot",
            'keywords' => "anti spam, bot killer",
            'author' => "Prasetyo Wicaksono",
            'link' => "http://www.fvck.me",
            'version' => "0.1",
            'required_bolt_version' => "1.0.2",
            'highest_bolt_version' => "1.0.2",
            'type' => "General",
            'first_releasedate' => "2013-10-02",
            'latest_releasedate' => "2013-10-02",
            'dependencies' => "",
            'priority' => 10
        );

        return $data;

    }

    /**
     * Initialize Bot Killer. Called during bootstrap phase.
     */
    function initialize()
    {

        // If yourextension has a 'config.yml', it is automatically loaded.
        // $foo = $this->config['bar'];

        // Initialize the Twig function
        $this->addTwigFunction('bounce', 'twigBounce');
        $this->app->match('/'.$this->config['bot_page'], array($this, 'alert'));

    }

    function alert()
    {

        $this->app['twig.loader.filesystem']->addPath(__DIR__.'/assets/');

        $defaultTemplate = 'bot.twig';

        if (!empty($this->config['template'])) {
            $template = $this->config['template'];
        } else {
            $template = $defaultTemplate;
        }

        return $this->app['twig']->render($template);

    }

    function reverseIp($ip)
    {

        $array = explode('.', $ip);

        return $array[3].'.'.$array[2].'.'.$array[1].'.'.$array[0];

    }

    /**
     * Twig function {{ bounce() }} in Bot Killer extension.
     */
    function twigBounce($name="")
    {

        $defaultLastSeen = 50;
        $defaultVisitor = 0;
        $defaultThreatScore = 30;

        if (!empty($this->config['max_last_seen'])) {
            $maxLastSeen = $this->config['max_last_seen'];
        } else {
            $maxLastSeen = $defaultLastSeen;
        }

        if (!empty($this->config['min_threat_score'])) {
            $minThreatScore = $this->config['min_threat_score'];
        } else {
            $minThreatScore = $defaultThreatScore;
        }

        if (!empty($this->config['min_visitor_allowed'])) {
            $minVisitorAllowed = $this->config['min_visitor_allowed'];
        } else {
            $minVisitorAllowed = $defaultVisitor;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $reversedIp = $this->reverseIp($ip);

        // Get response from honeypot server by send dns query
        $response = gethostbyname($this->config['private_key'].'.'.$reversedIp.'.'.'nsbl.httpbl.org');

        $response = explode('.', $response);

        //Is IP listed on honeypot server?
        if ($response[0] == '127') {
            if ($response[3] > $minVisitorAllowed) {
                if ($response[1] < $maxLastSeen) {
                    if ($response[2] > $minThreatScore) {
                        if ($this->config['db_log']) {
                            $this->app['log']->add("Blocked " . $ip , 9, '', 'BotKiller');
                        }

                        if ($this->config['txt_log']) {
                            $fh = fopen(__DIR__.'/blocked_ip.txt', 'a') or die("can't open file");
                            fwrite($fh, $ip." Blocked on ".date('Y-m-d H:i:s'));
                            fclose($fh);
                        }
                        
                        header('Location: '.$this->app['paths']['rooturl'].$this->config['bot_page']);
                        exit;
                    }
                }
            }
        }

    }

}


