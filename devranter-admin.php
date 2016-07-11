<h1>Devranter</h1>

<p>
    Devranter is an <b>unofficial</b> wordpress plugin for <a href="http://devrant.io">devRant.io</a>.<br>
    This plugin has not been developed and is not endorsed by devRant.io - therefore the awesome people at devRant will
    not provide support for this plugin.<br>
    This plugin has been developed by a fan - that's it :).<br>
</p>

<h3>Need help?</h3>
Go to my website to get help. <a href="http://chaensel.de/devranter" target="_blank">http://chaensel.de/devranter</a>.
<br>
Or send me an email to <a href="mailto:chris@chaensel.de">chris@chaensel.de</a>.<br>

<p>
    <h3>Like this plugin? Buy me a beer!</h3>
Every single dollar/ EURO I make with this plugin will go right to my beer dealer :D <br>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
    <input type="hidden" name="cmd" value="_donations">
    <input type="hidden" name="business" value="aviationcoder@googlemail.com">
    <input type="hidden" name="lc" value="US">
    <input type="hidden" name="no_note" value="0">
    <input type="hidden" name="currency_code" value="EUR">
    <input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHostedGuest">
    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
    <img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
</form>
</p>

<p>
<h3>By the way</h3>
This is my <strong>very first</strong> wordpress plugin - so go easy on me :)
<hr>
</p>


<p>
<form method="post" action="options.php">
    <?php settings_fields('devrant-settings-group'); ?>
    <?php do_settings_sections('devranter-options'); ?>
    <?php submit_button() ?>
</form>
</p>