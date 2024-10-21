## Create polls and voting for Contao Open Source CMS ##

![](https://img.shields.io/packagist/v/toteph42/voting.svg)
![](https://img.shields.io/packagist/l/toteph42/voting.svg)
![](https://img.shields.io/packagist/dt/toteph42/voting.svg)

This bundle is based on [contao-polls](https://github.com/codefog/contao-polls). It is sligthly modified,
a couple on bug fixes were applied and the whole bundle is made compatible with the developer rules defined for
[Contao 5.3](https://contao.org/de/).

## Setup a voting request

To setup a new vote request go to the `Voting` (in `CONTENT` tab)

![](images/voting1.png)

Then you can create a new record using the `New voting` button. The voting configuration form has many 
options, but they are prefilled with the ready-to-use values.

<img src="images/voting2.png" width="60%" height="60%" />

Next important thing is the behavior configuration. Here you have to set how the vooting behaves after user 
has or has not voted. The most optimal behavior has been set as the default, but you can adjust it to your needs anytime.

![](images/voting3.png)

Once you are done with the voting configuration, you have to create the voting options. Each option has the 
percentage bar displaying the current amount of the voting, if you have not sepcified a `Max. number of votes`. 
You can also view and manage the voting using the `voting` button in every row.

<img src="images/voting5.png" width="100%" height="100%" />

## Publish on the website

When the voting is ready, you can put it on the website as content element. You can either choose the voting manually or let the script find the most current one (based on voting settings).

![](images/voting5.png)

On your web sute it will look like

![](images/voting8.png)

On the web site you (and in back end) you will see the results (the provided `bundles/voting/style/voting.css` is
base on the [ODD Theme](https://contao-themes.net/theme-detail/odd.html). If you want to customize your `CSS` setting please create a new file `_custom.ss` in same directory.

![](images/voting6.png)

If you have specifies `Max. number of votes` then you will see a sligthly different screen.

![](images/voting7.png)

## Reset the poll

After all tests you can easily reset all votings using the `Reset voting` button at the top of the page:

Please enjoy!

If you enjoy my software, I would be happy to receive a donation.

<a href="https://www.paypal.com/donate/?hosted_button_id=DS6VK49NAFHEQ" target="_blank" rel="noopener">
  <img src="https://www.paypalobjects.com/en_US/DK/i/btn/btn_donateCC_LG.gif" alt="Donate with PayPal"/>
</a>

