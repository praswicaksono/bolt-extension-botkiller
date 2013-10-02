BotKiller
========

BotKiller is implementation of HoneyPot project. It aim to eliminate spammer or
bad behaviour visitor before they reach your website.

This extension will redirect unwanted visitor to custom page so they will not
see your site.

Configuration
-------------

CHMOD 777 or 755 blocked_ip.txt if you plan to record blocked IP into txt file

"All queries to http:BL should be run against your local DNS server which, if it does not have an authoritative answer, will hand the query off to a more authoritative DNS server"

For more documentation please visit http://www.projecthoneypot.org/httpbl_api.php

### Example config

<pre>
# This is the config file for the Bot Killer extension.

template: bot.twig
# Set Max seen bot in honeypot network 0-255
# Only bot last seen under 50 days will be blocked
max_last_seen: 50
# Only search engine bot allowed
# Please read http://www.projecthoneypot.org/httpbl_api.php for more information
min_visitor_allowed: 0
# Minimal threat score 0-255
# Please read http://www.projecthoneypot.org/httpbl_api.php for more infomation
min_threat_score: 100
# Honeypot private key
private_key: eevptxousdwz
# All naughty bot will redirected here
bot_page: go-away
db_log: true
txt_log: true
      
</pre>
 
### Usage

Put {{ bounce() }} in top of all your template
